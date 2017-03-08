<?php

namespace Application\AppBundle\Slack\Command\Absence;

use Application\AppBundle\Slack\Command\AbstractCommand;
use Application\AppBundle\Slack\Command\CommandInput;
use Application\AppBundle\Slack\Command\CommandOutput;
use Application\AppBundle\Slack\Command\SlackCommand;
use Domain\Model\Absence\Absence;
use Domain\UseCase\Absence\WhereIs;
use Infrastructure\File\AbsenceStorage;

class WhereIsCommand extends AbstractCommand implements SlackCommand, WhereIs\Responder
{
    private $output;

    public function configure()
    {
        $this->setRegex('/gdzie jest (.+)/iu');
    }

    public function execute(CommandInput $input, CommandOutput $output)
    {
        $this->output = $output;

        $name = trim($this->getPart(1));

        $useCase = new WhereIs(new AbsenceStorage());
        $useCase->execute(new WhereIs\Command(new \DateTime(), $name), $this);
    }

    public function entryNotFoundForPerson(string $person)
    {
        $this->output->setText($person.' jest (powinien/powinna być) dzisiaj w pracy!');
    }

    public function personIs(array $absenceData)
    {
        $this->output->setText($absenceData['person'].' '.Absence::reason($absenceData['type']));
    }
}
