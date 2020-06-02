<?php declare(strict_types=1);


namespace App\Backend\Console;

use App\Backend\Import\PimImport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Import extends Command
{
    protected static $defaultName = 'pim:import';

    /**
     * @var \App\Backend\Import\PimImport
     */
    private $pimImport;

    /**
     * @param \App\Backend\Import\PimImport $pimImport
     */
    public function __construct(PimImport $pimImport)
    {
        parent::__construct();
        $this->pimImport = $pimImport;
    }


    protected function configure() : void
    {
        $this->setDescription('Import product');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->pimImport->run();

        return 0;
    }
}
