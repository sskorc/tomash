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

class DelegationCommand extends AbstractCommand implements SlackCommand, TakeDelegation\Responder
{
    /** @var CommandOutput */
    private $output;

    public function configure()
    {
        $this->setRegex('/delegacja (.+)/iu');
    }

    public function execute(CommandInput $input, CommandOutput $output)
    {
        $this->output = $output;

        $period = $this->getPeriod($this->getPart(1));

        $useCase = new TakeDelegation(new AbsenceStorage());
        $useCase->execute(
            new TakeDelegation\Command(
                $input->getUsername(),
                $period['startDate'],
                $period['endDate']
            ),
            $this
        );
    }

    public function delegationTakenSuccessfully()
    {
        $this->output->setText('Nie wydaj za dużo! ;) :moneybag:');
    }

    public function failedToTakeDelegation(AbsenceException $exception)
    {
        $this->output->setText('Nigdzie nie jedziesz! Wracaj do roboty! :(');
    }
}
