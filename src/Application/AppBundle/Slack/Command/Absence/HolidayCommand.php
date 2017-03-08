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

class HolidayCommand extends AbstractCommand implements SlackCommand, TakeHoliday\Responder
{
    /** @var CommandOutput */
    private $output;

    public function configure()
    {
        $this->setRegex('/urlop (.+)/iu');
    }

    public function execute(CommandInput $input, CommandOutput $output)
    {
        $this->output = $output;

        $period = $this->getPeriod($this->getPart(1));

        $useCase = new TakeHoliday(new AbsenceStorage());
        $useCase->execute(
            new TakeHoliday\Command(
                $input->getUsername(),
                $period['startDate'],
                $period['endDate']
            ),
            $this
        );
    }

    public function holidayTakenSuccessfully()
    {
        $this->output->setText('Udanego wypoczynku! :) :sunny:');
    }

    public function failedToTakeHoliday(AbsenceException $exception)
    {
        $this->output->setText('W pracy nie pada!');
    }
}
