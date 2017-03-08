<?php

namespace Application\AppBundle\Slack\Command\Lunch;

use Application\AppBundle\Slack\Command\AbstractCommand;
use Application\AppBundle\Slack\Command\CommandInput;
use Application\AppBundle\Slack\Command\CommandOutput;
use Application\AppBundle\Slack\Command\SlackCommand;
use Domain\Exception\NotSupportedRestaurantException;
use Domain\Model\Lunch\Order;
use Domain\UseCase\Lunch\InitializeOrder;
use Infrastructure\File\OrderStorage;
use Slack\Message\Attachment;

class WeAreEatingCommand extends AbstractCommand implements SlackCommand, InitializeOrder\Responder
{
    /** @var CommandOutput */
    private $output;

    public function configure()
    {
        $this->setRegex('/(?:jemy|zamawiamy) (.+)/iu');
    }

    public function execute(CommandInput $input, CommandOutput $output)
    {
        $this->output = $output;

        $restaurant = $this->getPart(1);

        $command = new InitializeOrder\Command($restaurant);

        $useCase = new InitializeOrder(new OrderStorage());
        $useCase->execute($command, $this);
    }

    public function orderInitializedSuccessfully(Order $order)
    {
        $restaurant = $order->getRestaurant();
        $this->output->setText('OK, zbieram zamówienia do #'. $restaurant->getName());

        $menuItems = [];
        foreach ($restaurant->getMenu() as $menuItem) {
            $price = $menuItem->getPrice()->toFloat();
            $price = number_format($price, 2, ',', ' ') . ' zł';

            $menuItems[] = sprintf(
                '%d. %s [%s]',
                $menuItem->getPosition(),
                $menuItem->getName(),
                $price
            );
        }

        $attachment = new Attachment($restaurant->getFullName() .' Menu', implode($menuItems, "\n"));
        $this->output->setAttachment($attachment);
    }

    public function orderInitializationFailed(\Exception $e)
    {
        if ($e instanceof NotSupportedRestaurantException) {
            $this->output->setText('Nie ma takiej restauracji');

            return;
        }

        $this->output->setText('Coś się zapsuło i nie ma zamawiania :(');
    }
}
