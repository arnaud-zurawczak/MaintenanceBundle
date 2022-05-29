<?php

namespace Ady\Bundle\MaintenanceBundle\Command;

use Ady\Bundle\MaintenanceBundle\Drivers\DriverFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Create an unlock action.
 *
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DriverUnlockCommand extends Command
{
    /**
     * @var DriverFactory
     */
    private $driverFactory;

    public function __construct(DriverFactory $driverFactory)
    {
        parent::__construct();
        $this->driverFactory = $driverFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('ady:maintenance:unlock')
            ->setDescription('Unlock access to the site while maintenance...');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->confirmUnlock($input, $output)) {
            return 1;
        }

        $driver = $this->driverFactory->getDriver();

        $unlockMessage = $driver->getMessageUnlock($driver->unlock());

        $output->writeln('<info>'.$unlockMessage.'</info>');

        return 0;
    }

    /**
     * @param InputInterface   $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function confirmUnlock(InputInterface $input, OutputInterface $output): bool
    {
        $formatter = $this->getHelperSet()->get('formatter');

        if ($input->getOption('no-interaction')) {
            $confirmation = true;
        } else {
            // confirm
            $output->writeln([
                '',
                $formatter->formatBlock('You are about to unlock your server.', 'bg=green;fg=white', true),
                '',
            ]);

            $confirmation = $this->askConfirmation(
                'WARNING! Are you sure you wish to continue? (y/n) ',
                $input,
                $output
            );
        }

        if (!$confirmation) {
            $output->writeln('<error>Action cancelled!</error>');
        }

        return $confirmation;
    }

    /**
     * @param string $question
     * @param InputInterface   $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function askConfirmation(string $question, InputInterface $input, OutputInterface $output)
    {
        return $this->getHelper('question')
            ->ask($input, $output, new ConfirmationQuestion($question));
    }
}
