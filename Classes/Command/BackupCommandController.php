<?php
namespace Breadlesscode\Backups\Command;

use Neos\Flow\Cli\CommandController;
use Neos\Flow\Annotations as Flow;

use Breadlesscode\Backups\Service\BackupService;

class BackupCommandController extends CommandController
{
    /**
     * @Flow\Inject()
     * @var BackupService
     */
    protected $backupService;

    /**
     * creates a single backup of a specific package
     */
    public function createCommand(): void
    {
        if ($this->backupService->noStepsConfigured()) {
            $this->outputError('No backup steps configured. Please configure Breadlesscode.Backups.steps');
            $this->quit();
        }

        $this->output('Creating backup...');
        $this->backupService->createBackup();
        $this->outputLine('<success>success</success>');
    }

    /**
     * restores a single backup of a specific package
     */
    public function restoreCommand(string $name, bool $noConfirm = false): void
    {
        $shouldRestore = true;
        $backup = $this->backupService->getBackup($name);
        $steps = $this->backupService->getStepsInstances(
            $this->backupService->getTemporaryBackupPath(),
            $backup['meta']['steps']
        );
        // print warnings
        foreach ($steps as $stepClass => $step) {
            /** @var $step StepInterface */
            $message = $step->getRestoreWarning();

            if($message !== null) {
                $this->outputLine('<error>'.$stepClass.'</error>');
                $this->outputLine('<comment>'.$message.'</comment>');
                $this->outputLine();
            }
        }

        if ($noConfirm === false) {
            $shouldRestore = $this->output->askConfirmation('Are you sure you want to restore this Backup?', false);
        }

        if(!$shouldRestore) {
            $this->outputLine();
            $this->outputLine('<error>Canceled by user</error>');
            $this->quit();
        }

        $this->output('Restoring backup...');
        try {
            $this->backupService->restoreBackup($name);
            $this->outputLine('<success>success</success>');
        } catch (\Exception $e) {
            $this->outputError($e->getMessage());
        }
    }

    /**
     * lists all backups
     */
    public function listCommand($offset = 0, $limit = 60): void
    {
        $backups = $this->backupService->getBackups($offset, $limit);

        $backups = array_map(function($backup) {
            unset($backup['meta']);
            return $backup;
        }, $backups);

        $this->outputLine();
        $this->output->outputTable($backups, ['Name', 'Date', 'Relative date']);
    }

    /**
     * deletes backups
     */
    public function deleteCommand(string $name, bool $noConfirm = false): void
    {
        $confirmed = true;

        if (!$noConfirm) {
            $confirmed = $this->output->askConfirmation('Are you sure you want to delete this backup?');
        }

        if (!$confirmed) {
            $this->outputLine();
            $this->outputLine('<error>Canceled by user</error>');
            $this->quit();
        }

        $this->output('Deleting backup...');

        try {
            $this->backupService->deleteBackup($name);
            $this->outputLine('<success>success</success>');
        } catch (\Exception $e) {
            $this->outputError($e->getMessage());
        }
    }

    protected function outputError(string $message): void
    {
        $this->outputLine('<error>failed</error>');
        $this->outputLine();
        $this->outputLine('Reason:');
        $this->outputLine($message);
    }
}
