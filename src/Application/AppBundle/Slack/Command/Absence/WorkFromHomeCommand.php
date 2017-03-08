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

class WorkFromHomeCommand extends AbstractCommand implements SlackCommand, WorkFromHome\Responder
{
    /** @var CommandOutput */
    private $output;

    public function configure()
    {
        $this->setRegex('/(?:wfh|WFH) (.+)/iu');
    }

    public function execute(CommandInput $input, CommandOutput $output)
    {
        $this->output = $output;

        $period = $this->getPeriod($this->getPart(1));

        $useCase = new WorkFromHome(new AbsenceStorage());
        $useCase->execute(
            new WorkFromHome\Command(
                $input->getUsername(),
                $period['startDate'],
                $period['endDate']
            ),
            $this
        );
    }

    public function failedToWorkFormHome(AbsenceException $exception)
    {
        $this->output->setText('Nie poradzimy sobie BEZ Ciebie!');
    }

    public function workFromHomeSuccessfully()
    {
        $this->output->setText('Mam nadzieję, że jednak będziesz pracował ;) :house:');
    }
}
