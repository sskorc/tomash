<?php

namespace Application\AppBundle\Slack\Command\Absence;

use Application\AppBundle\Slack\Command\AbstractCommand;
use Application\AppBundle\Slack\Command\CommandInput;
use Application\AppBundle\Slack\Command\CommandOutput;
use Application\AppBundle\Slack\Command\SlackCommand;
use Domain\Model\Absence\Absence;
use Domain\UseCase\Absence\ListAbsent;
use Infrastructure\File\AbsenceStorage;
use Slack\Message\Attachment;

class WhoIsAbsentCommand extends AbstractCommand implements SlackCommand, ListAbsent\Responder
{
    /** @var CommandOutput */
    private $output;

    public function configure()
    {
        $this->setRegex('/nieobecni (.+)/');
    }

    public function execute(CommandInput $input, CommandOutput $output)
    {
        $this->output = $output;

        $period = $this->getPeriod($this->getPart(1));

        $useCase = new ListAbsent(new AbsenceStorage());
        $useCase->execute(new ListAbsent\Command($period['startDate']), $this);
    }

    public function allAreAtWork()
    {
        $this->output->setText('Wszyscy pracują. Niewiarygodne!');
    }

    public function absentWorkersListedSuccessfully(string $date, array $absenceData)
    {
        foreach ($absenceData as $user => $reason) {
            $lines[] = sprintf('%s %s', $user, Absence::reason($reason));
        }
        $attachment = new Attachment('Nieobecni '.$date, implode($lines, "\n"));
        $this->output->setAttachment($attachment);
    }

    public function absentWorkersListFailed()
    {
        $this->output->setText('Coś nie zadziałało. Trzeba policzyć ręcznie.');
    }
}
