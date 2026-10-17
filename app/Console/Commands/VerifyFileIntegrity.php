<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\RdbProject;
use App\Models\RdbProjectFiles;
use App\Models\RdbPublished;
use App\Models\RdbDip;
use App\Models\RdbResearcher;
use App\Models\ResearchConference;
use App\Models\ResearchNews;

class VerifyFileIntegrity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'file:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifies that all files referenced in the database exist in storage.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting file integrity verification...');
        $totalMissing = 0;

        // 1. RdbProject
        $this->line('Checking RdbProject...');
        foreach (RdbProject::cursor() as $project) {
            // Abstract
            $abstractPath = $project->getAbstractFilePath();
            if ($abstractPath && !Storage::exists($abstractPath)) {
                $this->warn("MISSING Abstract: Project ID {$project->pro_id}, Path: {$abstractPath}");
                $totalMissing++;
            }
            // Report
            $reportPath = $project->getReportFilePath();
            if ($reportPath && !Storage::exists($reportPath)) {
                $this->warn("MISSING Report: Project ID {$project->pro_id}, Path: {$reportPath}");
                $totalMissing++;
            }
        }

        // 2. RdbProjectFiles
        $this->line('Checking RdbProjectFiles...');
        foreach (RdbProjectFiles::cursor() as $file) {
            $path = $file->getFilePath(); // Uses accessor to handle legacy vs new path
            if ($path && !Storage::exists($path)) {
                $this->warn("MISSING Additional File: File ID {$file->id}, Project ID {$file->pro_id}, Path: {$path}");
                $totalMissing++;
            }
        }

        // 3. RdbPublished
        $this->line('Checking RdbPublished...');
        foreach (RdbPublished::cursor() as $published) {
            $path = $published->getPublishedFilePath();
            if ($path && !Storage::exists($path)) {
                $this->warn("MISSING Published File: Published ID {$published->id}, Path: {$path}");
                $totalMissing++;
            }
        }

        // 4. RdbDip
        $this->line('Checking RdbDip...');
        foreach (RdbDip::cursor() as $dip) {
             // Checking multiple potential files for RdbDip
             $dipFiles = [
                'dip_files' => $dip->getDipFilePath('dip_path', 'dip_files', 'uploads/dips'),
                'dip_data1_files' => $dip->getDipFilePath('dip_data1_path', 'dip_data1_files', 'uploads/dips'),
                'dip_data2_files_con' => $dip->getDipFilePath('dip_data2_con_path', 'dip_data2_files_con', 'uploads/dips'),
                'dip_data3_filesass1' => $dip->getDipFilePath('dip_data3_ass1_path', 'dip_data3_filesass1', 'uploads/dips'),
                'dip_data3_filesass2' => $dip->getDipFilePath('dip_data3_ass2_path', 'dip_data3_filesass2', 'uploads/dips'),
                'dip_data3_filesass3' => $dip->getDipFilePath('dip_data3_ass3_path', 'dip_data3_filesass3', 'uploads/dips'),
             ];
             foreach($dipFiles as $field => $path) {
                if ($path && !Storage::exists($path)) {
                    $this->warn("MISSING Dip File: Dip ID {$dip->dip_id}, Field: {$field}, Path: {$path}");
                    $totalMissing++;
                }
             }
        }
        
        // 5. RdbResearcher (Profile Image)
        $this->line('Checking RdbResearcher (Profile Images)...');
        foreach (RdbResearcher::cursor() as $researcher) {
            $path = $researcher->getProfileFilePath();
            if ($path && !Storage::exists($path)) {
                $this->warn("MISSING Researcher Image: Researcher ID {$researcher->researcher_id}, Path: {$path}");
                $totalMissing++;
            }
        }

        // 6. ResearchConference
        $this->line('Checking ResearchConference...');
        foreach (ResearchConference::cursor() as $conference) {
            if ($conference->con_img && !Storage::disk('public')->exists('uploads/conference/' . $conference->con_img)) {
                $this->warn("MISSING Conference Image (con_img): Conference ID {$conference->id}, Path: uploads/conference/{$conference->con_img}");
                $totalMissing++;
            }
            if ($conference->con_site_img && !Storage::disk('public')->exists('uploads/conference/' . $conference->con_site_img)) {
                $this->warn("MISSING Conference Image (con_site_img): Conference ID {$conference->id}, Path: uploads/conference/{$conference->con_site_img}");
                $totalMissing++;
            }
        }

        // 7. ResearchNews
        $this->line('Checking ResearchNews...');
        foreach (ResearchNews::cursor() as $news) {
            $path = $news->getNewsFilePath();
            if ($path && !Storage::exists($path)) {
                $this->warn("MISSING News File: News ID {$news->id}, Path: {$path}");
                $totalMissing++;
            }
        }

        if ($totalMissing > 0) {
            $this->error("Verification complete. Found {$totalMissing} missing files.");
        } else {
            $this->info('Verification complete. All files are accounted for.');
        }

        return 0;
    }
}