<?php

namespace Ady\Bundle\MaintenanceBundle\Command;

use Ady\Bundle\MaintenanceBundle\Drivers\AbstractDriver;
use Ady\Bundle\MaintenanceBundle\Drivers\DriverFactory;
use Ady\Bundle\MaintenanceBundle\Drivers\DriverTtlInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Create a lock action.
 *
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DriverLockCommand extends Command
{
    protected $ttl;

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
            ->setName('ady:maintenance:lock')
            ->setDescription('Lock access to the site while maintenance...')
            ->addArgument('ttl', InputArgument::OPTIONAL, 'Overwrite time to life from your configuration, doesn\'t work with file or shm driver. Time in seconds.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $driver = $this->getDriver();

        if ($input->isInteractive()) {
            if (!$this->askConfirmation('WARNING! Are you sure you wish to continue? (y/n)', $input, $output)) {
                $output->writeln('<error>Maintenance cancelled!</error>');

                return 1;
            }
        } elseif (null !== $input->getArgument('ttl')) {
            $this->ttl = $input->getArgument('ttl');
        } elseif ($driver instanceof DriverTtlInterface) {
            $this->ttl = $driver->getTtl();
        }

        // set ttl from command line if given and driver supports it
        if ($driver instanceof DriverTtlInterface) {
            $driver->setTtl($this->ttl);
        }

        $output->writeln('<info>'.$driver->getMessageLock($driver->lock()).'</info>');

        return 0;
    }

    /**
     * {@inheritdoc}
     * @throws \ErrorException
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $driver = $this->getDriver();
        $default = $driver->getOptions();

        $formatter = $this->getHelperSet()->get('formatter');

        if (null !== $input->getArgument('ttl') && !is_numeric($input->getArgument('ttl'))) {
            throw new \InvalidArgumentException('Time must be an integer');
        }

        $output->writeln([
            '',
            $formatter->formatBlock('You are about to launch maintenance', 'bg=red;fg=white', true),
            '',
        ]);

        $ttl = null;
        if ($driver instanceof DriverTtlInterface) {
            if (null === $input->getArgument('ttl')) {
                $output->writeln([
                    '',
                    'Do you want to redefine maintenance life time ?',
                    'If yes enter the number of seconds. Press enter to continue',
                    '',
                ]);

                $ttl = $this->askAndValidate(
                    $input,
                    $output,
                    sprintf('<info>%s</info> [<comment>Default value in your configuration: %s</comment>]%s ', 'Set time', $driver->hasTtl() ? $driver->getTtl() : 'unlimited', ':'),
                    function ($value) use ($default) {
                        if (!is_numeric($value) && null === $default) {
                            return null;
                        } elseif (!is_numeric($value)) {
                            throw new \InvalidArgumentException('Time must be an integer');
                        }

                        return $value;
                    },
                    1,
                    $default['ttl'] ?? 0
                );
            }

            $ttl = (int) $ttl;
            $this->ttl = 0 !== $ttl ? $ttl : $input->getArgument('ttl');
        } else {
            $output->writeln([
                '',
                sprintf('<fg=red>Ttl doesn\'t work with %s driver</>', get_class($driver)),
                '',
            ]);
        }
    }

    /**
     * Get driver.
     *
     * @return AbstractDriver
     *
     * @throws \ErrorException
     */
    private function getDriver(): AbstractDriver
    {
        return $this->driverFactory->getDriver();
    }

    /**
     * @param string          $question
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function askConfirmation(string $question, InputInterface $input, OutputInterface $output)
    {
        return $this->getHelper('question')
            ->ask($input, $output, new ConfirmationQuestion($question));
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $question
     * @param callable        $validator
     * @param int|null        $attempts
     * @param mixed           $default
     *
     * @return mixed
     */
    protected function askAndValidate(
        InputInterface $input,
        OutputInterface $output,
        string $question,
        callable $validator,
        ?int $attempts = 1,
        $default = null
    ) {
        $question = new Question($question, $default);
        $question->setValidator($validator);
        $question->setMaxAttempts($attempts);

        return $this->getHelper('question')
            ->ask($input, $output, $question);
    }
}
