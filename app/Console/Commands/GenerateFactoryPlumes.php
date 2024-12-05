<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FactoryPlumeService;

class GenerateFactoryPlumes extends Command
{
    protected $signature = 'plumes:generate';
    protected $description = 'Generate Gaussian plume TIF files for all factories';

    protected $plumeService;

    public function __construct(FactoryPlumeService $plumeService)
    {
        parent::__construct();
        $this->plumeService = $plumeService;
    }

    public function handle()
    {
        $this->info('Starting plume generation at: ' . now());

        try {
            $results = $this->plumeService->generateAllPlumes();

            foreach ($results as $factory => $filePath) {
                $this->info("Generated plume for {$factory}: {$filePath}");
            }

            $this->info('Plume generation completed successfully');

        } catch (\Exception $e) {
            $this->error('Error generating plumes: ' . $e->getMessage());
        }
    }
}