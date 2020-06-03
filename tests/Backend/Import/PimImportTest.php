<?php declare(strict_types=1);


namespace App\Tests\Backend\Import;



use App\Backend\Import\PimImport;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PimImportTest extends KernelTestCase
{
    public function test()
    {
        /** @var PimImport $pimImport */
        $pimImport = self::$container->get(PimImport::class);
        $pimImport->run();
    }
}
