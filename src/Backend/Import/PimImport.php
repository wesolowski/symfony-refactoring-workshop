<?php

namespace App\Backend\Import;

use App\Backend\Import\AlphaPimHelper as alpha_pim_helper;
use App\Entity\Article;
use App\Entity\Attribute;
use App\Entity\Category;
use App\Entity\Object2category;
use App\Repository\ArticleRepository;
use App\Repository\AttributeRepository;
use App\Repository\CategoryRepository;
use SimpleXMLElement;

declare(ticks=1);


class PimImport
{

    const XML_IMPORT_PATH_DEFAULT = 'XML_IMPORT_PATH_DEFAULT';
    const XML_IMPORT_STRUKTUR_FILE_DEFAULT = 'XML_IMPORT_STRUKTUR_FILE_DEFAULT';
    const XML_IMPORT_TEILE_FILE_DEFAULT = 'XML_IMPORT_TEILE_FILE_DEFAULT';
    const XML_STRUKTUR_PATTERN = '*Strukturexport*.xml';
    const XML_TEILE_PATTERN = '*Produktexport*.xml';
    const CSV_PREISE_PATTERN = '*preise*.csv';
    const STRUKTUR_MERKMALSDEFINITION_SELECTOR = 'Merkmalsdefinition';
    const STRUKTUR_WARENGRUPPEN_SELECTOR = 'Warengruppe';
    const STRUKTUR_UNTERWARENGRUPPEN_SELECTOR = 'Warenuntergruppen/Warenuntergruppe';

    /**
     * @var oxLegacyDb
     */
    protected $_oDb = null;
    protected $remove_images = false;
    protected $onWorkedProducts = [];
    protected $_aArticleMerkmaleMapping = [
        'Mengeneinheit' => [
            'field' => 'asy_packaging',
            'type' => 'int',
        ],
        'Mindestbestellmenge' => [
            'field' => 'asy_min_order',
            'type' => 'int',
        ],
        'Verkaufseinheit' => [
            'field' => 'unitname',
            'only_as_oxfield' => false,
        ],
        'Kurzbeschreibung' => 'shortdesc',
        'Langbeschreibung' => 'longdesc',
        'Produkt_Artikelname' => 'title',
        'Montageoption' => 'asy_installation',
        'Energie-Label-Wert' => 'ifellabel',
    ];

    protected $_aAllShops = [];
    protected $_blInitialized = false;
    protected $_sCsvPreise = null;
    protected $_sXmlImportPath = null;
    protected $_sXmlImportStrukturFile = null;
    protected $_sXmlImportTeileFile = null;
    protected $_aImportedAttributeKeys = [];
    protected $_aImportedAttributes = [];
    protected $_aImportedCategories = [];
    protected $_aVariantMerkmale = [];
    protected $_aImportedArticles = [];
    protected $_iCntArticles = 0;
    protected $mainCatSort = 0;
    protected $articleProductInStructur = [];
    protected $timer;

    /**
     * @var AlphaLogger
     */
    protected $_oLog = null;
    protected $_aLanguages = [];

    /**
     * @var oxLang
     */
    protected $_oLang = null;

    /**
     * @var oxUtilsObject
     */
    protected $_oUtils = null;

    /** @var  string import type */
    protected $type;
    /**
     * @var \App\Repository\AttributeRepository
     */
    private $attributeRepository;
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $objectManager;
    /**
     * @var \App\Repository\ArticleRepository
     */
    private $articleRepository;
    /**
     * @var \App\Repository\CategoryRepository
     */
    private $categoryRepository;

    public function __construct(
        \Doctrine\DBAL\Connection $connection,
        AttributeRepository $attributeRepository,
        \Doctrine\ORM\EntityManagerInterface $objectManager,
        ArticleRepository $articleRepository,
        CategoryRepository $categoryRepository

    )
    {
        $this->type = 'all';
        $this->_oDb = $connection;
        $this->_aLanguages = [
            (object)[
                'abbr' => 'de',
                'id' => '1',
            ],
            (object)[
                'abbr' => 'uk',
                'id' => '2',
            ],
        ];
        $this->attributeRepository = $attributeRepository;
        $this->objectManager = $objectManager;
        $this->articleRepository = $articleRepository;
        $this->categoryRepository = $categoryRepository;
    }


    /**
     *
     * to run the importer
     *
     * @param int $sXmlImportPath
     * @param int $sXmlImportStrukturFile
     * @param int $sXmlImportTeileFile
     *
     * @todo we can change between delta and full import
     *
     */
    public function run($sXmlImportPath = self::XML_IMPORT_PATH_DEFAULT, $sXmlImportStrukturFile = self::XML_IMPORT_STRUKTUR_FILE_DEFAULT, $sXmlImportTeileFile = self::XML_IMPORT_TEILE_FILE_DEFAULT, $sCsvPreise = self::CSV_PREISE_PATTERN)
    {


        $this->timer = microtime(true);
        if (!in_array($this->type, ['p', 'b'])) {
            $this->_initializeFiles($sXmlImportPath, $sXmlImportStrukturFile, $sXmlImportTeileFile, $sCsvPreise);
            $oStrukturXML = null;
            $oProductXML = null;
            $strukturExists = false;
            $produktExists = false;
            if (!in_array($this->type, ['g', 'cleanup']) || $sXmlImportPath != self::XML_IMPORT_PATH_DEFAULT) {
                $sStrukturCompletePath = $this->_sXmlImportPath . DIRECTORY_SEPARATOR . $this->_sXmlImportStrukturFile;

                if (file_exists($sStrukturCompletePath) && is_file($sStrukturCompletePath)) {
                    $oStrukturXML = simplexml_load_file($sStrukturCompletePath);
                    $strukturExists = true;
                }
            }
            if (in_array($this->type, ['a', 'e', 'g', 'all'])) {
                $sProductCompletePath = $this->_sXmlImportPath . DIRECTORY_SEPARATOR . $this->_sXmlImportTeileFile;

                if (file_exists($sProductCompletePath) && is_file($sProductCompletePath)) {
                    $oProductXML = simplexml_load_file($sProductCompletePath);
                    $produktExists = true;
                }
            }

            if (in_array($this->type, ['a', 'all', 'c']) && !$strukturExists && !$produktExists) {
                $this->_oLog->error('Keine Struktur und kein Produktexport vorhanden.');
                return;
            }
        }

        $artNumbs = [
            '55259627', '55263585', '55286718', '55263609', '55263625', '55134251', '55294565', '55267788', '55272298', '55258031', '55134250', '55258029', '55134260', '55259624', '55294561', '55258026', '55267880', '55236315', '55270516', '55294134', '55263597', '55284694', '55290582', '55272294', '55286722', '55220268', '55291562', '55258547', '55268503', '55268209', '55258028', '55257354', '55224273', '55227280', '55287286', '55208445', '55272633', '55267882', '55270526', '286790', '294570', '258023', '263613', '290582', '267640', '268504', '236316', '270531', '272300', '236311', '278994', '267633', '272330', '275515', '263599', '272333', '258482', '263631', '294568', '294134', '263589', '236313', '227278', '286722', '263624', '272334', '263590', '270526', '279005', '284695', '294566', '294135', '263617', '263610', '279758', '294562', '236274', '270522', '294572', '134428', '263591', '208446', '291562', '278963', '275520', '258568', '267630', '263603', '236318', '274448', '268981', '292136', '286791', '272295', '294138', '294141', '134260', '278962', '275663', '294567', '272339', '263626', '270518', '258536', '263609', '286724', '267632', '284698', '258015', '284696', '272753', '239122', '267637', '272633', '272634', '134426', '263596', '278993', '267641', '286799', '272337', '259627', '267788', '275518', '224273', '272336', '290590', '272297', '287289', '287290', '279007', '258532', '263619', '278995', '272322', '259628', '267880', '294137', '278997', '258026', '272340', '258028', '287286', '286725', '294564', '263627', '294569', '259629', '291565', '134255', '263606', '263600', '239119', '258014', '134256', '263608', '275521', '294565', '263629', '278992', '267635', '236322', '286798', '147049', '268501', '286720', '208447', '236302', '272327', '294140', '294561', '272323', '263611', '258025', '294563', '147048', '263597', '270520', '275522', '272335', '275661', '236305', '257356', '278964', '277552', '284697', '272301', '134251', '263601', '258027', '263595', '268208', '134246', '294139', '258018', '279000', '286718', '258030', '257354', '279002', '268502', '134250', '258021', '263607', '275516', '278990', '275662', '267634', '263586', '267638', '219760', '275513', '286748', '272341', '263604', '263623', '278999', '291564', '263598', '263628', '275658', '274439', '286750', '270528', '270523', '284699', '278998', '279183', '259625', '290583', '278988', '239116', '236272', '290591', '236304', '263594', '236303', '258024', '220270', '270530', '275659', '291563', '272331', '263630', '275514', '275523', '279006', '272294', '263587', '277551', '286792', '258537', '263592', '286796', '258029', '272324', '279003', '286787', '258559', '267789', '263625', '267881', '263593', '263585', '294136', '258547', '275519', '270521', '258031', '279004', '287291', '263615', '286795', '286721', '272299', '286719', '272328', '263616', '258017', '274449', '263602', '279001', '275660', '263605', '134258', '259626', '208448', '134253', '263614', '278991', '220268', '268503', '258020', '259630', '274438', '263621', '272332', '284694', '267631', '268211', '239123', '134248', '263620', '268212', '286793', '286789', '267636', '258022', '272329', '272298', '263612', '286749', '268209', '239117', '287288', '274440', '272296', '258539', '258540', '134245', '275664', '287287', '258538', '272325', '263632', '267639', '278989', '239131', '257355', '278996', '272338', '208445', '258535', '239132', '263588', '258019', '258571', '267882', '272326', '286723', '278965', '275517', '236315', '263618', '286751', '263622', '258016', '274450', '236325', '224275', '259624', '286797', '279757', '210853', '270529', '286794',
        ];


        if (($this->type == 'a' || $this->type == 'all') && $produktExists && $strukturExists) { // Produkte und Attribute
            echo 'Beginne Artikel Import ' . '(' . sprintf('%.3f', ((microtime(true) - $this->timer) / 60)) . ')' . PHP_EOL;
            echo "Start attributes\n";
            $this->_workWithAttributes($oStrukturXML);
            echo "Start articles\n";
            $this->_workWithArticles($oProductXML);
            if ($this->type == 'a') {
                $this->clearImageCache();
            }
            echo 'Ende Artikel Import' . '(' . sprintf('%.3f', ((microtime(true) - $this->timer) / 60)) . ')' . PHP_EOL;
        }
        if (($this->type == 'c' || $this->type == 'all' || $this->type == 'oc') && $strukturExists) { // Kategorien und Attributslogik
            echo 'Beginne Kategorie Struktur Import' . '(' . sprintf('%.3f', ((microtime(true) - $this->timer) / 60)) . ')' . PHP_EOL;
            $this->_workWithCategories($oStrukturXML);
            echo 'Ende Kategorie Struktur Import' . '(' . sprintf('%.3f', ((microtime(true) - $this->timer) / 60)) . ')' . PHP_EOL;
        }

//        if (in_array($this->type, ['g', 'a', 'all'])) {  // Crossseller & Zubehör
//            echo 'Beginne Crossseller Zubehör Import' . '(' . sprintf('%.3f', ((microtime(true) - $this->timer) / 60)) . ')' . PHP_EOL;
//            $this->_workWithAdditionalArticles($oProductXML);
//            echo 'Ende Crossseller Zubehör Import' . '(' . sprintf('%.3f', ((microtime(true) - $this->timer) / 60)) . ')' . PHP_EOL;
//        }

        //$this->_cleanup($oStrukturXML);

        echo "\n\nsuccess" . '(' . sprintf('%.3f', ((microtime(true) - $this->timer) / 60)) . ')' . PHP_EOL;
    }

    protected function _cleanup($oStrukturXML = null)
    {
        echo 'Beginne Cleanup' . PHP_EOL;

        /*if ($this->type == 'cleanup_products') {
            $this->cleanupProducts();
        }*/

        if (!is_null($oStrukturXML)) {
            $this->cleanupArticleStructure($oStrukturXML);
        }

        $this->clearCache();
        if (!in_array($this->type, ['p', 'b', 'nl'])) {
            $this->prepareLongDescs();
        }
        echo 'Ende Cleanup' . PHP_EOL;
    }

    protected function clearCache()
    {
        $this->_oLog->debug('Leere Cache');
        $this->deleteDirectory(getShopBasePath() . '/tmp');
        $this->deleteDirectory(getShopBasePath() . '/cache');
        mkdir(getShopBasePath() . '/tmp');
        mkdir(getShopBasePath() . '/cache');
    }

    protected function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }

        }

        return rmdir($dir);
    }

    /**
     *
     * to iterate over all articles in product xml export of pim
     *
     * @param SimpleXMLElement $oXMLElement
     */
    protected function _workWithArticles(SimpleXMLElement $oXMLElement)
    {
        /** @var SimpleXMLElement $oProductsXML */
        if ($oProductsXML = $oXMLElement->xpath('//Root/Produkt')) {
            //work on products -> products are the father articles in oxid
            foreach ($oProductsXML as $oProductXML) {
                /** @var SimpleXMLElement $oProductXML */
                $this->_workOnArticle($oProductXML);
            }
        }

        if ($oArticlesXML = $oXMLElement->xpath('//Root/Artikel')) {
            //work on articles -> articles are the variants
            // or better the buyable unit in oxid
            // not all products have variants
            foreach ($oArticlesXML as $oArticleXML) {
                /** @var SimpleXMLElement $oArticleXML */
                $this->_workOnArticle($oArticleXML);
            }
        }
    }

    /**
     * Crossseller und Zubehör werden erst nach dem Import zugeordnet,
     * um sicherzustellen das diese Artikel dann auch existieren
     *
     * @param SimpleXMLElement $oXMLElement
     */

    protected function _workWithAdditionalArticles(SimpleXMLElement $oXMLElement)
    {
        /** @var SimpleXMLElement $oProductsXML */
        if ($oProductsXML = $oXMLElement->xpath('//Root/Produkt')) {
            //work on products -> products are the father articles in oxid
            foreach ($oProductsXML as $oProductXML) {
                /** @var SimpleXMLElement $oProductXML */
                $this->_workOnAdditionalArticles($oProductXML);
            }
        }
        if ($oArticlesXML = $oXMLElement->xpath('//Root/Artikel')) {
            foreach ($oArticlesXML as $oArticleXML) {
                /** @var SimpleXMLElement $oArticleXML */
                $this->_workOnAdditionalArticles($oArticleXML, true);
            }
        }
    }

    /**
     * Zieht die Crosseller aus der XML und prüft,
     * ob diese evtl. vom Vaterartikel gezogen werden müssen
     *
     * @param SimpleXMLElement $oArticleXML
     * @param type $setParent
     *
     * @return type
     */
    protected function _workOnAdditionalArticles(SimpleXMLElement $oArticleXML, $setParent = false)
    {
        $this->_iCntArticles++;
        $sArticleNumber = alpha_pim_helper::xmlAttribute($oArticleXML, 'key');

        /** @var oxArticle $oArticle */
        $oArticle = oxNew('oxarticle');
        $oArticle->setEnableMultilang(false);
        $oArticle->loadByArtnum($sArticleNumber);
        if (!$oArticle->getId())
            return;
        $oArticle->clearZubehorAndCrosseller();
        $aCrosseller = $oArticleXML->xpath('Crossselling/Crossseller');
        if (!empty($aCrosseller)) {
            $this->addAddidionalArticle($aCrosseller, $oArticle);
        } elseif ($setParent !== false) {
            $this->addAddidionalArticleFromParent($oArticle);
        }
        $aZubehor = $oArticleXML->xpath('Zubehoer/Crossseller');
        if (!empty($aZubehor)) {
            $this->addAddidionalArticle($aZubehor, $oArticle, 'zubehoer');
        } elseif ($setParent !== false) {
            $this->addAddidionalArticleFromParent($oArticle, 'zubehoer');
        }
    }

    /**
     * Crossseller durchlaufen und zuordnen
     *
     * @param type $aCrosseller
     * @param type $oArticle
     * @param type $type
     */
    protected function addAddidionalArticle($aCrosseller, $oArticle, $type = 'crossseller')
    {
        foreach ($aCrosseller as $oCrossellerXML) {
            $crossellerArticle = oxNew('oxArticle');
            $crossellerArticle->loadByArtnum(alpha_pim_helper::xmlAttribute($oCrossellerXML, 'key'));
            if ($crossellerArticle->getId()) {
                if ($type === 'crossseller') {
                    $oArticle->addCrosseller($crossellerArticle->getId());
                } else {
                    $oArticle->addZubehor($crossellerArticle->getId());
                }
            }
        }
    }

    /**
     * Artikel ohne Crossseller? Dann die vom Vaterartikel nehmen
     *
     * @param type $oArticle
     * @param type $type
     *
     * @return type
     *
     */
    protected function addAddidionalArticleFromParent($oArticle, $type = 'crossseller')
    {
        $oParent = oxNew('oxarticle');
        $oParent->load($oArticle->oxarticles__oxparentid->value);
        if (!$oParent->getId())
            return;
        $table = $type === 'crossseller' ? 'oxobject2article' : 'oxaccessoire2article';
        $sQ = "INSERT INTO {$table} (OXID, OXOBJECTID, OXARTICLENID, OXSORT) "
            . "SELECT UUID_SHORT(), OXOBJECTID, ?, OXSORT FROM {$table} WHERE OXARTICLENID = ?";
        oxDb::getDb()->Execute($sQ, [$oArticle->getId(), $oParent->getId()]);
    }

    /**
     *
     * to work on attributes in structure xml of pim
     *
     * @param SimpleXMLElement $oMerkmalXML
     */
    protected function _workOnAttribute(SimpleXMLElement $oMerkmalXML)
    {
        $sTitle = alpha_pim_helper::xmlAttribute($oMerkmalXML, 'Name');
        $sCmiUUID = alpha_pim_helper::xmlAttribute($oMerkmalXML, 'key');


        $oAttribute = $this->attributeRepository->findOneBy(['cmiuuid' => $sCmiUUID]);
        if (!$oAttribute instanceof Attribute) {
            $oAttribute = new Attribute();
            $oAttribute->setTitle($sTitle);
        }

        /**
         * @todo mapping pim to oxid table
         */
        /** <Einheit>mm</Einheit> */
        $sUnit = '';
        if ($aEinheit = $this->_getSimpleMerkmal($oMerkmalXML, 'Einheit')) {
            $sUnit = (string)$aEinheit[0];
        }

        /** <OXID-Merkmal_an_FACT-Finder>ja</OXID-Merkmal_an_FACT-Finder> */
        $blExportToFF = false;
        if ($aMerkmalToFF = $this->_getSimpleMerkmal($oMerkmalXML, 'OXID-Merkmal_an_FACT-Finder')) {
            $blExportToFF = ((string)$aMerkmalToFF[0] == 'ja');
        };

        /** <OXID-Merkmalsname_an_FACT-Finder_xx>Abteilbreite~~mm</OXID-Merkmalsname_an_FACT-Finder_xx> */
        $sFFExportTitle = '';
        if ($aMerkmalToFFName = $this->_getSimpleMerkmal($oMerkmalXML, 'OXID-Merkmalsname_an_FACT-Finder_xx')) {
            $sFFExportTitle = (string)$aMerkmalToFFName[0];
        }

        /** <OXID-Merkmal_im_Warenkorb_anzeigen>nein</OXID-Merkmal_im_Warenkorb_anzeigen> */
        $blDisplayInBasket = false;
        if ($aMerkmalInBasket = $this->_getSimpleMerkmal($oMerkmalXML, 'OXID-Merkmal_im_Warenkorb_anzeigen')) {
            $blDisplayInBasket = ((integer)$aMerkmalInBasket[0] != 'nein');
        };

        /** <OXID-Merkmalssortierung>10013</OXID-Merkmalssortierung> */
        $iPos = 99999;
        if ($aSort = $this->_getSimpleMerkmal($oMerkmalXML, 'OXID-Merkmalssortierung')) {
            $iPos = (integer)$aSort[0];
        };

        /** <Variantenmerkmal>1</Variantenmerkmal> * */
        $iVariantenMerkmal = 0;
        if ($aVariantenMerkmal = $this->_getSimpleMerkmal($oMerkmalXML, 'Variantenmerkmal')) {
            $iVariantenMerkmal = (integer)$aVariantenMerkmal[0];
            if ($iVariantenMerkmal > 0) {
                $this->_aVariantMerkmale[$iVariantenMerkmal] = $sTitle;
            }
        };

        $oAttribute->setTitle($sTitle);
        $oAttribute->setSwffexporttoff($blExportToFF);
        $oAttribute->setSwffexporttitle($sFFExportTitle);
        $oAttribute->setPos($iPos);
        $oAttribute->setDisplayinbasket($blDisplayInBasket);
        $oAttribute->setPos($iPos);
        $oAttribute->setCmiuuid($sCmiUUID);
        $oAttribute->setUnit($sUnit);
        $oAttribute->setVariantAttributeSort($iVariantenMerkmal);

        $this->objectManager->persist($oAttribute);
        $this->objectManager->flush();

        $this->_aImportedAttributes[] = $oAttribute->getId();
        $this->_aImportedAttributeKeys[] = $sCmiUUID;
    }

    /**
     *
     * initializing xml files or file (file only for delta import in this version)
     *
     * @param $sXmlImportPath
     * @param $sXmlImportStrukturFile
     * @param $sXmlImportTeileFile
     */
    public
    function _initializeFiles($sXmlImportPath, $sXmlImportStrukturFile, $sXmlImportTeileFile, $sCsvPreise)
    {
        if ($sXmlImportPath == self::XML_IMPORT_PATH_DEFAULT) {
            $this->_sXmlImportPath = __DIR__ . '/files';
        } else {
            $this->_sXmlImportPath = $sXmlImportPath;
        }

        if ($sXmlImportStrukturFile == self::XML_IMPORT_STRUKTUR_FILE_DEFAULT) {
            if ($aFiles = glob($this->_sXmlImportPath . DIRECTORY_SEPARATOR . self::XML_STRUKTUR_PATTERN)) {
                //enable the oldest file
                $this->_sXmlImportStrukturFile = basename($aFiles[0]);
            }
        }
        if ($sXmlImportTeileFile == self::XML_IMPORT_TEILE_FILE_DEFAULT) {
            if ($aFiles = glob($this->_sXmlImportPath . DIRECTORY_SEPARATOR . self::XML_TEILE_PATTERN)) {
                //enable the oldest file
                $this->_sXmlImportTeileFile = basename($aFiles[0]);
            }
        }
        if ($csvFiles = glob($this->_sXmlImportPath . DIRECTORY_SEPARATOR . $sCsvPreise)) {
            $this->_sCsvPreise = basename($csvFiles[0]);
        }
    }

    /**
     *
     * helper methode to get a simple node of xml
     *
     * @param SimpleXMLElement $oXml
     * @param $sName string
     */
    protected function _getSimpleMerkmal(SimpleXMLElement $oXML, $sName, $blOnlyFirst = true)
    {
        $sXpathQuery = '(descendant::' . $sName . ')';
        if ($blOnlyFirst) {
            $sXpathQuery .= '[1]';
        }
        return alpha_pim_helper::xmlNodeByQuery($oXML, $sXpathQuery);
    }

    /**
     *
     * helper methode to get a merkmal node with name $sName
     *
     * @param $oXML SimpleXMLElement
     * @param $sName string
     */
    protected function _getMerkmal($oXML, $sName)
    {
        return $this->_getSimpleMerkmal($oXML, 'Merkmale[1]/Merkmal[@Name=\'' . $sName . '\']');
    }

    /**
     *
     * work and save category
     *
     * @param $oCategoryXML SimpleXMLElement
     * @param $mxParent string|oxCategory
     */
    protected function _workOnCategory($oCategoryXML, $mxParent = 'oxrootid', $mainCat = null)
    {
        echo "Working on Category " . alpha_pim_helper::xmlAttribute($oCategoryXML, 'Name') . "\n";
        /** @var Category $oParentCategory */
        if (is_string($mxParent) && $mxParent == 'oxrootid') {
            $sParentId = $mxParent;
        } elseif (is_string($mxParent) && $mxParent != 'oxrootid') {
            $oParentCategory = oxNew('oxcategory');
            $oParentCategory->load($mxParent);
            $sParentId = $mxParent;
        } elseif ($mxParent instanceof oxCategory) {
            $sParentId = $mxParent->getId();
        }

        $sTitle = alpha_pim_helper::xmlAttribute($oCategoryXML, 'Name');
        $sCmiUUID = alpha_pim_helper::xmlAttribute($oCategoryXML, 'uuid');

        $oCategory = $this->categoryRepository->findOneBy(['cmiuuid' => $sCmiUUID]);
        if (!$oCategory instanceof Category) {
            $oCategory = new Category();
            $oCategory->setTitle($sTitle);
            if (isset($sParentId)) {
                $oCategory->setParentid($sParentId);
            }
        }

        if (!$oCategory->getId()) {
            $oCategory->setCmiuuid($sCmiUUID);
        }

        $sTemplate = alpha_pim_helper::xmlAttribute($oCategoryXML, 'Warengruppen_Template');

        $iCattype = 0;
        if ($aCattype = $this->_getMerkmal($oCategoryXML, 'Warengruppentyp')) {
            $iCattype = (int)$aCattype[0]->Wert;
        }

        $iSort = $mainCat ? ++$this->mainCatSort : 0;
        if ($sSort = alpha_pim_helper::xmlAttribute($oCategoryXML, 'Order_Nr')) {
            $iSort = (integer)$sSort;
        }

        if ($aCrosssellingTitle = $this->_getMerkmal($oCategoryXML, 'Cross-selling-title')) {
            $crosssellingtitle = alpha_pim_helper::clearFromPIMTags((string)$aCrosssellingTitle[0]->Wert_xx);
            $crosssellingtitle_1 = alpha_pim_helper::clearFromPIMTags((string)$aCrosssellingTitle[0]->Wert_xx);
            $crosssellingtitle_2 = alpha_pim_helper::clearFromPIMTags((string)$aCrosssellingTitle[0]->Wert_nl);
        }

        $aData = [
            'parentid' => $sParentId ?? null,
            'sort' => $iSort ?? null,
            'active' => true,
            'hidden' => false,
            'title' => $sTitle ?? null,
            'template' => $sTemplate ?? null,
            'asy_cattype' => $iCattype ?? null,
            'cmiuuid' => $sCmiUUID ?? null,
            'crosssellingtitle' => $crosssellingtitle ?? null,
            'crosssellingtitle_1' => $crosssellingtitle_1 ?? null,
            'crosssellingtitle_2' => $crosssellingtitle_2 ?? null,
        ];

        $sichtbar = strtolower(alpha_pim_helper::xmlAttribute($oCategoryXML, 'sichtbar'));
        if (!empty($sichtbar) && $sichtbar == 'nein') {
            $aData['hidden'] = true;
        }

        $setangebot = 0;
        if ($aSetangebot = $this->_getMerkmal($oCategoryXML, 'Warengruppentyp SET-Artikel')) {
            $setangebot = (int)$aSetangebot[0]->Wert;
        }
        $aData['asy_setcategory'] = (bool)$setangebot;

        /**
         * multilingual fields
         * Merkmal[@Name=
         */
        if ($oNameMerkmal = $this->_getMerkmal($oCategoryXML, 'Produkt/Artikelname')) {
            $oNameMerkmal = $oNameMerkmal[0];
            foreach ($this->_aLanguages as $oxLang) {
                if ($oxLang->abbr == 'de') {
                    $sTranslatedValue = alpha_pim_helper::clearFromPIMTags((string)$oNameMerkmal->Wert_xx, null);
                    $sField = "title";
                } else {
                    $sWertPropertyName = sprintf('Wert_%s', $oxLang->abbr);
                    if (isset($oNameMerkmal->{$sWertPropertyName})) {
                        $sTranslatedValue = alpha_pim_helper::clearFromPIMTags((string)$oNameMerkmal->{$sWertPropertyName}, null);
                        $sField = sprintf("title_%d", $oxLang->id);
                    } else {
                        //go forward we are strict here! ;-)
                        continue;
                    }
                }
                $aData[$sField] = $sTranslatedValue;
            }
        }

        if ($aShortDescription = $this->_getMerkmal($oCategoryXML, 'Kurzbeschreibung')) {
            $aShortDescription = $aShortDescription[0];
            foreach ($this->_aLanguages as $oxLang) {
                if ($oxLang->abbr == 'de') {
                    $sTranslatedValue = alpha_pim_helper::clearFromPIMTags((string)$aShortDescription->Wert_xx, null);
                    $sField = "shortdesc";
                } else {
                    $sWertPropertyName = sprintf('Wert_%s', $oxLang->abbr);
                    if (isset($aShortDescription->{$sWertPropertyName})) {
                        $sTranslatedValue = alpha_pim_helper::clearFromPIMTags((string)$aShortDescription->{$sWertPropertyName}, null);
                        $sField = sprintf("shortdesc_%d", $oxLang->id);
                    } else {
                        //go forward we are strict here! ;-)
                        continue;
                    }
                }
                $aData[$sField] = $sTranslatedValue;
            }
        }

        if ($aLongDescription = $this->_getMerkmal($oCategoryXML, 'Langbeschreibung')) {
            $aLongDescription = $aLongDescription[0];
            foreach ($this->_aLanguages as $oxLang) {
                if ($oxLang->abbr == 'de') {
                    $sTranslatedValue = alpha_pim_helper::clearFromPIMTags((string)$aLongDescription->Wert_xx);
                    $sField = "longdesc";
                } else {
                    $sWertPropertyName = sprintf('Wert_%s', $oxLang->abbr);
                    if (isset($aLongDescription->{$sWertPropertyName})) {
                        $sTranslatedValue = alpha_pim_helper::clearFromPIMTags((string)$aLongDescription->{$sWertPropertyName});
                        $sField = sprintf("longdesc_%d", $oxLang->id);
                    } else {
                        //go forward we are strict here! ;-)
                        continue;
                    }
                }
                $aData[$sField] = $sTranslatedValue;
            }
        }

        $oCategory->setParentid($aData["parentid"] ?? null);
        $oCategory->setSort($aData["sort"] ?? null);
        $oCategory->setActive($aData["active"] ?? null);
        $oCategory->setHidden($aData["hidden"] ?? null);
        $oCategory->setTitle($aData["title"] ?? null);
        $oCategory->setTemplate($aData["template"] ?? null);
        $oCategory->setAsyCattype($aData["asy_cattype"] ?? null);
        $oCategory->setCmiuuid($aData["cmiuuid"] ?? null);
        $oCategory->setCrosssellingtitle($aData["crosssellingtitle"] ?? null);
        $oCategory->setCrosssellingtitle1($aData["crosssellingtitle_1"] ?? null);
        $oCategory->setCrosssellingtitle2($aData["crosssellingtitle_2"] ?? null);
        $oCategory->setAsySetcategory($aData["asy_setcategory"] ?? null);
        $oCategory->setShortdesc($aData["shortdesc"] ?? null);
        $oCategory->setLongdesc($aData["longdesc"] ?? null);

        $this->objectManager->persist($oCategory);
        $this->objectManager->flush();

        $this->_aImportedCategories[] = $oCategory->getId();

        //produkte -> varianten nur verarbeiten, wenn nicht einfach die kategorien definiert werden sollen
        if ($this->type != 'oc') {
            $this->_workOnCategoryArticles($oCategoryXML, $oCategory);
        }

        if ($oChildCategories = $oCategoryXML->xpath('Warenuntergruppen/Warenuntergruppe')) {
            //child categories avalaible
            foreach ($oChildCategories as $oSubCategoryXML) {
                /** @var SimpleXMLElement $oSubCategoryXML */
                $this->_workOnCategory($oSubCategoryXML, $oCategory);
            }
        }


    }

    protected function _prepareVariantSelect($oVariantXML, $oFatherXML, $variantVars)
    {
        $sArtnum = alpha_pim_helper::xmlAttribute($oVariantXML, 'key');
        $oVariantArticle = $this->articleRepository->findOneByArtnum($sArtnum);
        if ($oVariantArticle instanceof Article && $oVariantAttributesList = $oVariantArticle->getAttribute() and count($oVariantAttributesList)) {
            foreach ($oVariantAttributesList as $oAttribute) {
                $attributevalue = $oAttribute->getValue();
                $attributetitle = $oAttribute->getAttr()->getTitle();
                if (isset($variantVars[$attributetitle])) {
                    if (!in_array($attributevalue, $variantVars[$attributetitle])) {
                        $variantVars[$attributetitle][] = $attributevalue;
                    }
                }
            }
        }
        return $variantVars;
    }

    protected function _workOnVariant($oVariantXML, $oFatherXML, $variantVars, $variantVars_1, $variantVars_2, $variantVars_3)
    {
        $sArtnum = alpha_pim_helper::xmlAttribute($oVariantXML, 'key');
        $this->articleProductInStructur[] = $sArtnum;

        /*if(!in_array($sArtnum, [
            270917,
            270900,
            214994,
            214981,
            214987,
            214995,
            214982,
            214985,
            214989,
            270909,
            270910,
            214998,
            214980,
            270897,
            270928,
            270870,
            270898,
            214983,
            270880,
            214988,
            214991,
            270920,
            270887,
            214984,
            214992,
            270899,
            270927,
            270908,
            214996,
            270918,
            270890,
            270888,
            270919,
            270889,
            214986,
            214990,
            270907,
            214999,
            214993,
            214997
        ])) return;*/
        $oVariantArticle = $this->articleRepository->findOneByArtnum($sArtnum);
        if ($oVariantArticle instanceof Article) {
            if ($oVariantAttributesList = $oVariantArticle->getAttribute() and count($oVariantAttributesList)) {
                $variantvalue = [];
                foreach ($oVariantAttributesList as $oAttribute) {

                    $attributetitle = $oAttribute->getAttr()->getTitle();

                    if (!empty($attributetitle) && array_key_exists($attributetitle, $variantVars)) {
                        $attributevalue = $oAttribute->getValue();
                        $variantvalue[] = $attributevalue;
                    }
                }

                $sVariantSelect = implode(' | ', $variantvalue);
            }

            $this->_oDb->executeUpdate('update article set varselect=? where id=?', [
                $sVariantSelect ?? '',
                $oVariantArticle->getId(),
            ]);
        }
    }

    /**
     *
     * helper methode to set model properties dynamically by array
     *
     * @param $aData
     * @param $sTableName
     * @param oxI18n $oObject
     */
    protected function _setFieldValuesByArray($aData, $sTableName, oxI18n $oObject)
    {
        foreach ($aData as $sFieldName => $mxFieldValue) {
            $sPropertyName = $sTableName . '__' . $sFieldName;
            $oObject->{$sPropertyName} = new oxField($mxFieldValue);
        }
    }

    /**
     *
     * work on all attributes of structure xml of pim
     *
     * @param $oStrukturXML
     */
    protected function _workWithAttributes(SimpleXMLElement $oStrukturXML)
    {
        if ($oMerkmalsstruktur = $oStrukturXML->xpath('//' . self::STRUKTUR_MERKMALSDEFINITION_SELECTOR)) {
            foreach ($oMerkmalsstruktur as $oMerkmalXML) {
                /** <OXID-Merkmalssortierung>10013</OXID-Merkmalssortierung> */
                if ($aSort = $this->_getSimpleMerkmal($oMerkmalXML, 'OXID-Merkmalssortierung') and (int)$aSort[0] > 0) {
                    $this->_workOnAttribute($oMerkmalXML);
                };
            }
        }
        $this->_deleteNotUsedAttributes();
    }

    /**
     *
     * work on all categories of structure xml of pim
     *
     * @param $oStrukturXML
     *
     * @throws object
     */
    protected function _workWithCategories(SimpleXMLElement $oStrukturXML)
    {
        if ($oMainCategories = $oStrukturXML->xpath('//' . self::STRUKTUR_WARENGRUPPEN_SELECTOR)) {
            foreach ($oMainCategories as $oCategoryXML) {
                $this->_workOnCategory($oCategoryXML, 'oxrootid', true);
            }
        } else {
            $sError = 'Oh no dude no categories avalaible.';
            $this->_oLog->addError($sError);
        }
        echo "end workon categorie" . PHP_EOL;
        echo "start cleanduplicate articles" . PHP_EOL;
        echo "end cleanduplicate articles" . PHP_EOL;
        echo "start _deletenotuses Categories" . PHP_EOL;
        $this->_deleteNotUsedCategories();
        echo "end _delete" . PHP_EOL;
        echo "start build actegory tree" . PHP_EOL;
        echo "end category tree" . PHP_EOL;
    }

    /**
     * deletes categories which are not in the struktur xml file
     */
    protected function _deleteNotUsedCategories()
    {
        echo "befor if in delete not used Categories";
        if (!empty($this->_aImportedCategories)) {
            echo "in if in delete not used Categories";
            $sIds = "'" . join("','", $this->_aImportedCategories) . "'";
            $sCategorySql = 'select * from category where id not in (' . $sIds . ')';
            // this new feature!!!!
        }
        echo "nach if in delete not used Categories";
    }

    /**
     * deletes attributes which are not in the struktur xml file
     */
    protected function _deleteNotUsedAttributes()
    {
        if (!empty($this->_aImportedAttributes)) {
            $sIds = "'" . join("','", $this->_aImportedAttributes) . "'";
            // this new feature!!!!
        }
    }


    /**
     *
     * work on a single article and save
     *
     * @param SimpleXMLElement $oArticleXML
     */
    protected function _workOnArticle(SimpleXMLElement $oArticleXML)
    {
        echo "Working on Article " . (string)$oArticleXML['Name'] . "\n";
        $this->_iCntArticles++;
        $aData = [];
        $aAttributes = [];
        $sArticleNumber = alpha_pim_helper::xmlAttribute($oArticleXML, 'key');
        $articleNumberPrefix = substr($sArticleNumber, 0, 2);
        $productNumber = (string)$oArticleXML['Produkt'];

        $oArticle = $this->articleRepository->findOneBy(['artnum' => $sArticleNumber]);

        if (!$oArticle instanceof Article) {
            $oArticle = new Article();
            $oArticle->setArtnum($sArticleNumber);
        }

        if ($aXMLMerkmale = $oArticleXML->xpath('Merkmale/Merkmal')) {
            [$aData, $aAttributes, $oArticle] = $this->_workOnArticleMerkmale($aData, $aXMLMerkmale, $aAttributes, $oArticle);
        }

        /**
         * varianten Langtext
         */
        /*if ($oArticle->oxarticles__oxparentid->value) {
            $oParent = oxNew('oxarticle');
            $oParent->load($oArticle->oxarticles__oxparentid->value);
            $aData['oxlongdesc'] = $oParent->oxarticles__oxlongdesc->value;
            $aData['oxlongdesc_1'] = $oParent->oxarticles__oxlongdesc_1->value;
            $aData['oxlongdesc_2'] = $oParent->oxarticles__oxlongdesc_2->value;
            $aData['oxlongdesc_3'] = $oParent->oxarticles__oxlongdesc_3->value;
        }*/

        $aData['artnum'] = $sArticleNumber;


        //um produkte zu identifizieren auf den prefix 55 achten
        if (!empty($productNumber) and $articleNumberPrefix != 55) {
            $oParentArticle = $this->articleRepository->findOneBy(['artnum' => $productNumber]);
            if ($oParentArticle instanceof Article) {
                $aData['parentid'] = $oParentArticle->getId();
            }
        } elseif (empty($productNumber) and $articleNumberPrefix != 55) {
            if ($oArticle->getId()) {
                $aData['parentid'] = null;
            }
            $aData['oxvarselect'] = null;
            $aData['oxvarselect_1'] = null;
            $aData['oxvarselect_2'] = null;
            $aData['oxvarname'] = null;
            $aData['oxvarname_1'] = null;
            $aData['oxvarname_2'] = null;
        }

        if ($oArticleXML['Variantenmerkmale']) {
            $aData['alphabytes_variantenmerkmale'] = str_replace(', ', ' | ', (string)$oArticleXML['Variantenmerkmale']);
        }

        //$oArticle->assign($aData);
        $oArticle->setAsyPackaging($aData["asy_packaging"] ?? null);
        $oArticle->setAsyMinOrder($aData["asy_min_order"] ?? null);
        $oArticle->setAsyInstallation($aData["asy_installation"] ?? null);
        $oArticle->setTitle($aData["title"] ?? null);
        $oArticle->setShortdesc($aData["shortdesc"] ?? null);
        $oArticle->setLongdesc($aData["longdesc"] ?? null);
        $oArticle->setUnitname($aData["unitname"] ?? null);
        $oArticle->setAsyDeltextStandard1($aData["asy_deltext_standard_1"] ?? null);
        $oArticle->setAsyDeltextStandardSchweiz($aData["asy_deltext_standard_schweiz"] ?? null);
        $oArticle->setAsyDeltextStandard2($aData["asy_deltext_standard_2"] ?? null);
        $oArticle->setArtnum($aData["artnum"] ?? null);
        $oArticle->setAlphabytesVariantenmerkmale($aData["alphabytes_variantenmerkmale"] ?? null);
        $oArticle->setAsyDeltextStandard($aData["asy_deltext_standard"] ?? null);
        $oArticle->setArtnum(str_replace('WT', '', $aData["artnum"]));
        $oArticle->setParentid($aData["parentid"] ?? null);

        $this->objectManager->persist($oArticle);
        $this->objectManager->flush();
        $this->_aImportedArticles[] = $sArticleNumber;

        if ($aAttributes) {
            $this->_saveArticleAttributesFromArray($aAttributes, $oArticle, $oArticleXML);
        }

    }


    /**
     *
     * prepare article attributes and article properties (<-- to save in article table!)
     *
     * @param $aData
     * @param $aXMLMerkmale
     * @param $aAttributes
     * @param $oArticle oxArticle
     *
     * @return mixed
     * @internal param SimpleXMLElement $oArticleXML
     * @internal param $aArticleMerkmaleMapping
     */
    protected function _workOnArticleMerkmale($aData, $aXMLMerkmale, $aAttributes, $oArticle)
    {
        foreach ($aXMLMerkmale as $oMerkmalXML) {
            /** @var SimpleXMLElement $oMerkmalXML */
            $sKey = alpha_pim_helper::xmlAttribute($oMerkmalXML, 'key');
            if (isset($this->_aArticleMerkmaleMapping[$sKey])) {
                /**
                 * @todo die bilder behandeln wir einzeln!!
                 */
                if ($sKey != 'Web-Bild' && $sKey != 'Icons') {
                    /** a real article value */
                    //$aData[$this->_aArticleMerkmaleMapping[$sKey]] = '';
                    if (isset($oMerkmalXML->Wert)) {
                        if (is_array($this->_aArticleMerkmaleMapping[$sKey])) {
                            $value = alpha_pim_helper::clearFromPIMTags((string)$oMerkmalXML->Wert);
                            switch ($this->_aArticleMerkmaleMapping[$sKey]['type']) {
                                case 'int':
                                    $value = (int)str_replace(',', '.', $value);
                                    break;
                                default:
                                    break;
                            }
                            $field = $this->_aArticleMerkmaleMapping[$sKey]['field'];
                            if (isset($this->_aArticleMerkmaleMapping[$sKey]['only_as_oxfield']) && $this->_aArticleMerkmaleMapping[$sKey]['only_as_oxfield'] === false) {
                                //das merkmal wird auch als oxid attribute benötigt
                                $aAttributes = $this->declareOxidAttribute($aAttributes, $oMerkmalXML, $sKey);
                            }
                        } else {
                            $value = alpha_pim_helper::clearFromPIMTags((string)$oMerkmalXML->Wert);
                            $field = $this->_aArticleMerkmaleMapping[$sKey];
                        }
                        $aData[$field] = $value;
                    } elseif (isset($oMerkmalXML->Wert_xx)) {
                        //$aData[$this->_aArticleMerkmaleMapping[$sKey]] = alpha_pim_helper::clearFromPIMTags((string)$oMerkmalXML->Wert_xx);
                        /**
                         * @todo multilingual refactoring
                         */
                        // multilingual field!!
                        if (is_array($this->_aArticleMerkmaleMapping[$sKey])) {
                            $field = $this->_aArticleMerkmaleMapping[$sKey]['field'];
                            if (isset($this->_aArticleMerkmaleMapping[$sKey]['only_as_oxfield']) && $this->_aArticleMerkmaleMapping[$sKey]['only_as_oxfield'] === false) {
                                //das merkmal wird auch als oxid attribute benötigt
                                $aAttributes = $this->declareOxidAttribute($aAttributes, $oMerkmalXML, $sKey);
                            }
                        } else {
                            $field = $this->_aArticleMerkmaleMapping[$sKey];
                        }
                        foreach ($this->_aLanguages as $oxLang) {
                            if ($oxLang->abbr == 'de') {
                                if ($sKey == 'Langbeschreibung') {
                                    $sTranslatedValue = alpha_pim_helper::clearFromPIMTags((string)$oMerkmalXML->Wert_xx, '<p><ul><li><strong><br/><br><br /><b>', false);
                                } else {
                                    $sTranslatedValue = alpha_pim_helper::clearFromPIMTags((string)$oMerkmalXML->Wert_xx, null);
                                }
                                $sField = sprintf("%s", $field);
                            } else {
                                $sWertPropertyName = sprintf('Wert_%s', $oxLang->abbr);
                                if (isset($oMerkmalXML->{$sWertPropertyName})) {
                                    if ($sKey == 'Langbeschreibung') {
                                        $sTranslatedValue = alpha_pim_helper::clearFromPIMTags((string)$oMerkmalXML->{$sWertPropertyName}, '<p><ul><li><strong><br/><br><br /><b>', false);
                                    } else {
                                        $sTranslatedValue = alpha_pim_helper::clearFromPIMTags((string)$oMerkmalXML->{$sWertPropertyName}, null);
                                    }
                                } else {
                                    //go forward we are strict here! ;-)
                                    continue;
                                }
                                $sField = sprintf("%s_%d", $field, $oxLang->id);
                            }
                            if (!empty($sTranslatedValue)) {
                                $aData[$sField] = $sTranslatedValue;
                            }
                        }
                    }
                }
            } else {
                $aAttributes = $this->declareOxidAttribute($aAttributes, $oMerkmalXML, $sKey);
            }
        }
        return [$aData, $aAttributes, $oArticle];
    }

    /**
     *
     * save the article attributes
     *
     * @param $aAttributes
     * @param $oArticle
     */
    protected function _saveArticleAttributesFromArray($aAttributes, $oArticle, $articleXML)
    {
        //beim umbenennen des wertes, Ã¤ndert sich die "uuid", deshlab alle attributwerte fÃ¼r den artikel lÃ¶schen
        $this->_oDb->executeQuery('delete from object2attribute where object_id=?', [$oArticle->getId()]);
        foreach ($aAttributes as $sAttributeUUID => $mxAttributeValue) {
            $oAttribute = $this->attributeRepository->findOneBy(['cmiuuid' => $sAttributeUUID]);
            if ($oAttribute instanceof Attribute) {
                $aValues = [];
                $sInsertAttributeSQL = 'insert into object2attribute (object_id, attr_id';
                $aValues = array_merge($aValues, [
                    $oArticle->getId(),
                    $oAttribute->getId(),
                ]);
                $sInsertAttributeValuesSQL = '(?, ?';
                $attrData = $this->attributeRepository->findOneBy(['cmiuuid' => $sAttributeUUID]);
                if ($articleXML['Variantenmerkmale']) {
                    $sInsertAttributeSQL .= ', alpha_variantmerkmal';
                    $merkmale = explode(',', (string)$articleXML['Variantenmerkmale']);
                    $merkmale = array_map('trim', $merkmale);
                    if (in_array($oAttribute->getCmiuuid(), $merkmale)) {
                        $aValues = array_merge($aValues, [
                            1,
                        ]);
                        $sInsertAttributeValuesSQL .= ', ?';
                    } else {
                        $sInsertAttributeValuesSQL .= ', ?';
                        $aValues = array_merge($aValues, [
                            0,
                        ]);
                    }
                }
                $aQueryParameter = [$oArticle->getId(), $oAttribute->getId()];
                if (!is_array($mxAttributeValue)) {
                    $aQueryParameter[] = $mxAttributeValue;
                    $sInsertAttributeSQL .= ', value';
                    $aValues = array_merge($aValues, [
                        trim($mxAttributeValue),
                    ]);
                    $sInsertAttributeValuesSQL .= ', ?';
                } else {
                    foreach ($mxAttributeValue as $sAttributeKey => $sAttritbuteValue) {
                        if (strpos($sAttributeKey, '_0')) {
                            $sAttributeKey = strstr($sAttributeKey, '_0', true);
                        }
                        $aValues = array_merge($aValues, [
                            trim($sAttritbuteValue),
                        ]);
                        $sInsertAttributeValuesSQL .= ',?';
                        $sInsertAttributeSQL .= ', ' . $sAttributeKey;
                    }
                }


                $sInsertAttributeSQL .= ') values ' . $sInsertAttributeValuesSQL . ')';

                $this->_oDb->executeQuery($sInsertAttributeSQL, $aValues);
            }
        }
    }

    /**
     * @param SimpleXMLElement $oArtikelXML
     * @param Category $oCategory
     */
    protected function _addArticleToCategory($oArtikelXML, Category $oCategory)
    {
        /** @var SimpleXMLElement $oArtikelXML */
        $sArtnum = alpha_pim_helper::xmlAttribute($oArtikelXML, 'key');

        $oArticle = $this->articleRepository->findOneBy(['artnum' => $sArtnum]);

        if ($oArticle instanceof Article) {
            $sArticleID = $oArticle->getId();
            //save article $oArticle to category $oCategory
            $sSelect = "select 1 from object2category where catnid=? and objectid=?";
            $result = $this->_oDb->executeQuery($sSelect, [$oCategory->getId(), $sArticleID])->fetch();
            if (!$result) {
                $oObject2Cat = new Object2category();
                $oObject2Cat->setCatnid($oCategory->getId());
                $oObject2Cat->setObjectid($sArticleID);
                $this->objectManager->persist($oObject2Cat);
                $this->objectManager->flush();
            }
        }
    }


    /**
     * @param $oArtikelXML
     * @param $oVariants
     *
     * @return array
     */
    protected function saveVariantsOnArticle($oArtikelXML, $oVariants)
    {
        /** @var SimpleXMLElement $oArtikelXML */
        $sParentArtnum = alpha_pim_helper::xmlAttribute($oArtikelXML, 'key');
        $oParentArticle = $this->articleRepository->findOneByArtnum($sParentArtnum);
        if ($oParentArticle instanceof Article) {
            foreach ($oVariants as $oVariantXML) {
                $sVariantArtnum = alpha_pim_helper::xmlAttribute($oVariantXML, 'key');

                $oVariantArticle = $this->articleRepository->findOneByArtnum($sVariantArtnum);
                if ($oVariantArticle instanceof Article) {
                    $oVariantArticle->setParentid($oParentArticle->getId());
                    $this->objectManager->persist($oVariantArticle);
                }
            }
            $this->objectManager->flush();
        }
        return [$oParentArticle, $oVariantXML];
    }

    /**
     * @param $sIcon
     * @param $sKey
     * @param $object
     *
     * @return object
     * @internal param $aFiles
     */
    protected function workwithpictures($sIcon, $sKey, $object)
    {
        $aFiles = [];
        $filePath = $this->_sXmlImportPath . DIRECTORY_SEPARATOR . $sIcon;
        if (file_exists($filePath)) {

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $filePath);

            $aFiles['myfile']['name'][$sKey] = $sIcon;
            $aFiles['myfile']['type'][$sKey] = $mime;
            $aFiles['myfile']['tmp_name'][$sKey] = $this->_sXmlImportPath . DIRECTORY_SEPARATOR . $sIcon;
            $aFiles['myfile']['error'][$sKey] = 0;
            $aFiles['myfile']['size'][$sKey] = filesize($this->_sXmlImportPath . DIRECTORY_SEPARATOR . $sIcon);
            /** @var oxUtilsFile $files */
            $files = oxRegistry::get('oxUtilsFile');
            $aFiletype = explode("@", $sKey);
            $sObjectKey = $aFiletype[1];
            $object->{$sObjectKey} = new oxField('');
            $object = $files->processFiles($object, $aFiles, true, false);
        }
        return $object;
    }

    protected function _workWithCategoryArticles($oStrukturXML)
    {
        $oCategories = array_merge(
            $oStrukturXML->xpath('//' . self::STRUKTUR_WARENGRUPPEN_SELECTOR),
            $oStrukturXML->xpath('//' . self::STRUKTUR_UNTERWARENGRUPPEN_SELECTOR)
        );
        if (empty($oCategories)) {
            $this->_oLog->addError('Oh no dude no categories avalaible.');
            return;
        }
        foreach ($oCategories as $oCategoryXML) {
            $this->_workOnCategoryArticles($oCategoryXML);
        }
    }

    protected function _workOnCategoryArticles($oCategoryXML, $oCategory = null)
    {
        $aAllArticleNumbers = [];
        $oArtikelListe = $oCategoryXML->xpath('Produktliste/Produkt');
        if (!empty($oArtikelListe)) {
            //set articles to categories
            foreach ($oArtikelListe as $oArtikelXML) {
                $artikelArtnum = (string)$oArtikelXML['key'];
                $this->articleProductInStructur[] = $artikelArtnum;
                $aAllArticleNumbers[] = $artikelArtnum;

                //if($artikelArtnum != 55214999) continue;

                $oParentArticle = $this->articleRepository->findOneByArtnum($artikelArtnum);

                //produkte müssen auch in die kategorien...
                $this->_addArticleToCategory($oArtikelXML, $oCategory);

                if ($oParentArticle instanceof Article) {
                    if (array_key_exists($artikelArtnum, $this->onWorkedProducts)) {
                        $cntVariants = count($oArtikelXML->xpath('Artikelliste/Artikel'));
                        continue;
                    }

                    $variantVars = [];
                    $variantVars_1 = [];
                    $variantVars_2 = [];
                    $variantVars_3 = [];
                    /** @var SimpleXMLElement[] $oVariants */
                    if ($oVariants = $oArtikelXML->xpath('Artikelliste/Artikel')) {
                        //at first set articles father

                        $this->saveVariantsOnArticle($oArtikelXML, $oVariants);
                        //at second set new variant selector to parent
                        /** @var Article $oFirstVariantArticle */
                        $aVariantAttributes = [];
                        $attrCheck = [];
                        foreach ($oVariants as $variant) {
                            $oVariantArticle = $this->articleRepository->findOneByArtnum(alpha_pim_helper::xmlAttribute($variant, 'key'));
                            foreach ($oVariantArticle->getAttribute() as $key => $attrValue) {

                                $title = $attrValue->getAttr()->getTitle();
                                if (!in_array($title, $attrCheck)) {
                                    $attrCheck[] = $title;
                                    $aVariantAttributes[$attrValue->getId()] = [
                                        'title' => $title,
                                    ];
                                }
                            }
                        }

                        if (!empty($aVariantAttributes)) {
                            foreach ($aVariantAttributes as $key => $oVariantAttribute) {
                                $variantVars[$oVariantAttribute['title']] = [];
                            }
                            foreach ($oVariants as $oVariantXML) {
                                $variantVars = $this->_prepareVariantSelect($oVariantXML, $oArtikelXML, $variantVars);
                            }

                            $sVariantVarname = implode(' | ', array_keys($variantVars));
                        }

                        if (is_null($oParentArticle)) {
                            continue;
                        }

                        $this->_oDb->executeUpdate('update article set varname=? where id=?', [
                            $sVariantVarname ?? '',
                            $oParentArticle->getId(),
                        ]);
                        foreach ($oVariants as $oVariantXML) {
                            $this->_workOnVariant($oVariantXML, $oArtikelXML, $variantVars, $variantVars_1, $variantVars_2, $variantVars_3);
                        }
                        $this->onWorkedProducts[$artikelArtnum] = count($oVariants);
                    }
                }
            }
        }

        $oArtikelListe = $oCategoryXML->xpath('Artikelliste/Artikel');
        $aArticlenumbers = [];
        if (!empty($oArtikelListe)) {
            //set articles to categories
            foreach ($oArtikelListe as $oArtikelXML) {
                $artikelArtnum = (string)$oArtikelXML['key'];
                $aArticlenumbers[] = $artikelArtnum;
                /** @var oxArticle $oParentArticle */
                $oParentArticle = $this->articleRepository->findOneByArtnum($artikelArtnum);
                //produkte müssen auch in die kategorien...
                $this->_addArticleToCategory($oArtikelXML, $oCategory);
                if ($oParentArticle instanceof Article) {
                    $this->articleProductInStructur[] = $artikelArtnum;
                }
            }

        }

        $aAllArticleNumbers = array_merge($aArticlenumbers, $aAllArticleNumbers);
        if (count($aAllArticleNumbers) > 0) {
            $this->_clearOldCategoryAssignments($aAllArticleNumbers, $oCategory->getId());
        }
    }

    protected function _clearOldCategoryAssignments($articlenumbers, $catid)
    {
        $oDb = $this->_oDb;
        $sQuery = "    
            DELETE 
            FROM 
                object2category
            WHERE
                catnid = '" . $catid . "'
                AND objectid NOT IN (
                    SELECT
                        art.id
                    FROM
                        article art
                    WHERE
                        art.artnum IN ('" . implode("','", $articlenumbers) . "')
                )
        ";
        $oDb->executeQuery($sQuery);

    }

    protected function _workWithArticlesToCategories($oStrukturXML)
    {
        $oMainCategories = $oStrukturXML->xpath('//' . self::STRUKTUR_WARENGRUPPEN_SELECTOR);
        if (!empty($oMainCategories)) {
            foreach ($oMainCategories as $oCategoryXML) {
                $this->_workOnArticlesToCategories($oCategoryXML);
            }
        }
        $oMainCategories = $oStrukturXML->xpath('//' . self::STRUKTUR_UNTERWARENGRUPPEN_SELECTOR);
        if (!empty($oMainCategories)) {
            foreach ($oMainCategories as $oCategoryXML) {
                $this->_workOnArticlesToCategories($oCategoryXML);
            }
        }
    }

    protected function _workOnArticlesToCategories($oCategoryXML)
    {
        /** @var SimpleXMLElement $oCategoryXML */
        $sCmiUUID = alpha_pim_helper::xmlAttribute($oCategoryXML, 'uuid');
        $oCategory = oxNew('oxcategory');
        if (!$oCategory->loadByCmiUUID($sCmiUUID)) {
            return false;
        }
        if ($oProduktListe = $oCategoryXML->xpath('Produktliste/Produkt')) {
            foreach ($oProduktListe as $oProduktXML) {
                $this->_addArticleToCategory($oProduktXML, $oCategory);
                if ($oArtikelListe = $oProduktXML->xpath('Artikelliste/Artikel')) {
                    foreach ($oArtikelListe as $oArtikelXML) {
                        $this->_addArticleToCategory($oArtikelXML, $oCategory);
                    }
                }
            }
        }

        if ($oEinzelArtikelListe = $oCategoryXML->xpath('Artikelliste/Artikel')) {
            foreach ($oEinzelArtikelListe as $oEinzelArtikelXML) {
                $this->_addArticleToCategory($oEinzelArtikelXML, $oCategory);
            }
        }

        return true;
    }


    /**
     * Frühstückt die kritischen Punkte nach dem Import ab,
     * bei denen z.B. verknüpfte Artikel während des Imports noch nicht existieren
     */
    protected function prepareLongDescs()
    {
        $this->_oLog->debug('Langbeschreibung für Artikel setzen');
        $select = 'select a.OXID, b.OXPARENTID from oxartextends a, oxarticles b WHERE a.OXID = b.OXID';
        $articles = $this->_oDb->getAll($select);
        foreach ($articles as $article) {
            if (!empty($article['OXPARENTID']))
                $this->setArtikelLangebeschreibungFromParent($article);
        }
    }

    /**
     *  Falls Artikel keine Langebeschreibung haben, wird die vom Vaterartikel genommen.
     *
     * @param type $article
     */
    protected function setArtikelLangebeschreibungFromParent($article)
    {
        $select = 'SELECT OXLONGDESC, OXLONGDESC_1, OXLONGDESC_2, OXLONGDESC_3 FROM oxartextends WHERE OXID LIKE ?';
        $descriptions = $this->_oDb->getRow($select, [$article['OXPARENTID']]);
        $sQ = "UPDATE oxartextends a SET OXLONGDESC = ?, OXLONGDESC_1 = ?, OXLONGDESC_2 = ?, OXLONGDESC_3 = ? WHERE OXID LIKE ? ";
        oxDb::getDb()->Execute($sQ, [$descriptions['OXLONGDESC'], $descriptions['OXLONGDESC'], $descriptions['OXLONGDESC_2'], $descriptions['OXLONGDESC_3'], $article['OXID']]);
    }

    protected function cleanupArticleStructure($oStrukturXML = null)
    {
        if (!is_null($oStrukturXML)) {
            $produkte = $oStrukturXML->xpath('//Produktliste/Produkt');
            $artikel = $oStrukturXML->xpath('//Artikelliste/Artikel');
            $cntProdukte = count($produkte);
            $cntArtikel = count($artikel);
            if ($cntProdukte > 0 && $cntArtikel > 0) {
                $this->_oDb->execute('DROP TABLE alpha_pim_struktur');
                $this->_oDb->execute('CREATE TABLE alpha_pim_struktur ( artikelNr varchar(255) not null , primary key (artikelNr))');
                foreach ($produkte as $produkt) {
                    $this->_oDb->execute('replace into alpha_pim_struktur values(?)', [$produkt['key']]);
                }
                foreach ($artikel as $art) {
                    $this->_oDb->execute('replace into alpha_pim_struktur values(?)', [$art['key']]);
                }
                $this->_oDb->execute('update oxarticles oa set oa.oxactive = 0 where oa.oxartnum not in (select artikelNr from alpha_pim_struktur)');
            }
        }
    }

    protected function _getAllShops()
    {
        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        $sQuery = "
            SELECT
                oxid as id,
                alpha_country_alias as country
            FROM
              oxshops
            WHERE 
              1
        ";
        return $oDb->getAll($sQuery);
    }


    /**
     * Moves current seo record to seo history table
     *
     * @param string $sId object id
     * @param int $iShopId active shop id
     * @param int $iLang object language
     * @param string $sType object type (if you pass real object - type is not necessary)
     * @param string $sNewId new object id, mostly used for static url updates (optional)
     */
    protected function _copyToHistory($sId, $iShopId, $iLang, $sType = null, $sNewId = null)
    {
        $oDb = oxDb::getDb();
        $sObjectid = $sNewId ? $oDb->quote($sNewId) : 'oxobjectid';
        $sType = $sType ? "oxtype =" . $oDb->quote($sType) . " and" : '';
        $iLang = (int)$iLang;

        // moving
        $sSub = "select $sObjectid, MD5( LOWER( oxseourl ) ), oxshopid, oxlang, now() from oxseo
                 where {$sType} oxobjectid = " . $oDb->quote($sId) . " and oxshopid = " . $oDb->quote($iShopId) . " and
                 oxlang = {$iLang} and oxexpired = '1'";
        $sQ = "replace oxseohistory ( oxobjectid, oxident, oxshopid, oxlang, oxinsert ) {$sSub}";
        $oDb->execute($sQ);
    }

    /**
     * @param $aAttributes
     * @param $oMerkmalXML
     * @param $sKey
     *
     * @return array
     */
    protected function declareOxidAttribute($aAttributes, $oMerkmalXML, $sKey)
    {
//a simple attribute
        if (isset($oMerkmalXML->Wert)) {
            //non multilingual
            if (!empty($oMerkmalXML->Wert)) {
                foreach ($this->_aLanguages as $oxLang) {
                    $sTranslatedValue = alpha_pim_helper::clearFromPIMTags((string)$oMerkmalXML->Wert);
                    if ($oxLang->abbr == 'de') {
                        $sField = "value";
                    } else {
                        $sField = sprintf("value_%d", $oxLang->id);
                    }
                    $aAttributes[$sKey][$sField] = $sTranslatedValue;
                }

            }

        } elseif (isset($oMerkmalXML->Wert_xx)) {
            $aAttributes[$sKey] = [];
            //multilingual values
            foreach ($this->_aLanguages as $oxLang) {
                if ($oxLang->abbr == 'de') {
                    $sTranslatedValue = alpha_pim_helper::clearFromPIMTags((string)$oMerkmalXML->Wert_xx);
                    $sField = "value";
                } else {
                    $sWertPropertyName = sprintf('Wert_%s', $oxLang->abbr);
                    if (isset($oMerkmalXML->{$sWertPropertyName})) {
                        $sTranslatedValue = alpha_pim_helper::clearFromPIMTags((string)$oMerkmalXML->{$sWertPropertyName});
                        $sField = sprintf("value_%d", $oxLang->id);
                    } else {
                        //go forward we are strict here! ;-)
                        continue;
                    }
                }
                $aAttributes[$sKey][$sField] = $sTranslatedValue;
            }

        }
        return $aAttributes;
    }
}
