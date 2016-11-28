<?php

namespace Application\AppBundle\SlackCommand\Lunch;

use Application\AppBundle\SlackCommand\AbstractCommand;
use Domain\Model\Lunch\MenuItem;
use Domain\Model\Lunch\Order;
use Domain\UseCase\Lunch\AddItemToOrder;
use Infrastructure\File\OrderStorage;
use Slack\Channel;
use Slack\User;
use Spatie\Regex\Regex;

class IAmEatingCommand extends AbstractCommand implements AddItemToOrder\Responder
{
    public function execute(string $message, User $user, Channel $channel)
    {
        parent::execute($message, $user, $channel);

        $regex = Regex::match('/(?:jem|biore|biorę|dla mnie) (\w+) (\d{1,3})/iu', $message);

        if ($regex->hasMatch()) {
            $this->addItemToOrder($regex->group(1), $regex->group(2));

            return true;
        }

        return false;
    }

    protected function addItemToOrder(string $restaurant, int $position)
    {
        $command = new AddItemToOrder\Command($restaurant, $this->user->getId(), $position);

        $useCase = new AddItemToOrder(new OrderStorage());
        $useCase->execute($command, $this);
    }

    public function successfullyAddedItemToOrder(Order $order, string $userName, MenuItem $addedMenuItem)
    {
        $price = $addedMenuItem->getPrice()->toFloat();
        $price = number_format($price, 2, ',', ' ') . ' zł';

        $this->reply($addedMenuItem->getName() . ' dla Ciebie za ' . $price);
    }

    public function addingItemToOrderFailed(\Exception $e)
    {
        $this->reply('nie udało mi się zarejestrować Twojego zamówienia');
    }
}