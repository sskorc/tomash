<?php

namespace Application\AppBundle\Slack\Command\Absence;

use Application\AppBundle\Slack\Command\AbstractCommand;
use Application\AppBundle\Slack\Command\CommandInput;
use Application\AppBundle\Slack\Command\CommandOutput;
use Application\AppBundle\Slack\Command\SlackCommand;
use Domain\Exception\AbsenceException;
use Domain\UseCase\Absence\TakeDelegation;
use Domain\UseCase\Absence\TakeHoliday;
use Domain\UseCase\Absence\TakeSickLeave;
use Domain\UseCase\Absence\WorkFromHome;
use Infrastructure\File\AbsenceStorage;

class SickLeaveCommand extends AbstractCommand implements SlackCommand, TakeSickLeave\Responder
{
    /** @var CommandOutput */
    private $output;

    public function configure()
    {
        $this->setRegex('/(?:zwolnienie|l4|L4) (.+)/iu');
    }

    public function execute(CommandInput $input, CommandOutput $output)
    {
        $this->output = $output;

        $period = $this->getPeriod($this->getPart(1));

        $useCase = new TakeSickLeave(new AbsenceStorage());
        $useCase->execute(
            new TakeSickLeave\Command(
                $input->getUsername(),
                $period['startDate'],
                $period['endDate']
            ),
            $this
        );
    }

    public function sickLeaveTakenSuccessfully()
    {
        $this->output->setText('Szybkiego powrotu do zdrowia! :face_with_thermometer:');
    }

    public function failedToTakeSickLeave(AbsenceException $exception)
    {
        $this->output->setText('Nie symuluj! Wracaj do roboty! :(');
    }
}
