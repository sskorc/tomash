<?php

namespace Application\AppBundle\Slack\Command\Lunch;

use Application\AppBundle\Slack\Command\AbstractCommand;
use Application\AppBundle\Slack\Command\CommandInput;
use Application\AppBundle\Slack\Command\CommandOutput;
use Application\AppBundle\Slack\Command\SlackCommand;
use Domain\Model\Lunch\MenuItem;
use Domain\Model\Lunch\Order;
use Domain\UseCase\Lunch\AddItemToOrder;
use Infrastructure\File\OrderStorage;

class IAmEatingCommand extends AbstractCommand implements SlackCommand, AddItemToOrder\Responder
{
    /** @var CommandOutput */
    private $output;

    public function configure()
    {
        $this->setRegex('/(?:jem|biore|biorę|dla mnie) (\w+) (\d{1,3})(.*)/iu');
    }

    public function execute(CommandInput $input, CommandOutput $output)
    {
        $this->output = $output;

        $restaurant = $this->getPart(1);
        $position = $this->getPart(2);
        $annotation = $this->getPart(3);

        $command = new AddItemToOrder\Command($restaurant, $input->getUsername(), $position, $annotation);

        $useCase = new AddItemToOrder(new OrderStorage());
        $useCase->execute($command, $this);
    }

    public function successfullyAddedItemToOrder(
        Order $order,
        string $userName,
        MenuItem $addedMenuItem,
        string $annotation
    ) {
        $price = $addedMenuItem->getPrice()->toFloat();
        $price = number_format($price, 2, ',', ' ') . ' zł';

        if (!empty($annotation)) {
            $text = $addedMenuItem->getName() . ' (' . trim($annotation) . ')' . ' dla Ciebie za ' . $price;
        } else {
            $text = $addedMenuItem->getName() . ' dla Ciebie za ' . $price;
        }
        $this->output->setText($text);
    }

    public function addingItemToOrderFailed(\Exception $e)
    {
        $this->output->setText('nie udało mi się zarejestrować Twojego zamówienia');
    }
}
