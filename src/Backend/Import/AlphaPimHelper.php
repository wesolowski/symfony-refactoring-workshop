<?php

namespace App\Backend\Import;

use SimpleXMLElement;

class AlphaPimHelper
{
    /**
     * @param SimpleXMLElement $oXml
     * @return array Countries
     */
    public static function getCountries(SimpleXMLElement $oXml)
    {
        if ($sCountries = static::xmlAttribute($oXml, 'fuer_Land')) {
            $aCountries = explode(',', $sCountries);
            $aCountries = array_map('trim', $aCountries);
        }
        return $aCountries;
    }

    /**
     * @param $oXml SimpleXMLElement
     * @param $sAttribite string
     * @return string
     */
    public static function xmlAttribute($oXml, $sAttribite)
    {
        if (isset($oXml[$sAttribite])) {
            return (string)$oXml[$sAttribite];
        }
    }

    /**
     * @param SimpleXMLElement $oXml
     * @param $sQuery
     * @return SimpleXMLElement[]
     */
    public static function xmlNodeByQuery(SimpleXMLElement $oXml, $sQuery)
    {
        return $oXml->xpath($sQuery);
    }


    public static function clearFromPIMTags($sValue, $allowable_tags = '<p><ul><li><strong><br/><br><br /><b>', $htmlDecode = true)
    {
        $allowable_tags .= strtoupper($allowable_tags);
        $stripped = strip_tags($sValue, $allowable_tags);
        if ($htmlDecode) {
            $stripped = html_entity_decode($stripped, null);
        } // if
        $value = preg_replace("/<([a-zA-Z][a-z0-9]*)[^>]*?(\/?)>/i", '<$1$2>', $stripped);
        return $value;
    }
}
