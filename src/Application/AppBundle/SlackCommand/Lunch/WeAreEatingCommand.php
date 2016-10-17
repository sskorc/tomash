<?php

namespace Application\AppBundle\SlackCommand\Lunch;

use Application\AppBundle\SlackCommand\TempAbstractCommand;
use Domain\Exception\NotSupportedRestaurantException;
use Domain\Model\Lunch\Order;
use Domain\UseCase\Lunch\InitializeOrder;
use Infrastructure\File\OrderStorage;
use Slack\Channel;
use Slack\Message\Attachment;
use Slack\Message\MessageBuilder;
use Slack\User;

class WeAreEatingCommand extends TempAbstractCommand implements InitializeOrder\Responder
{
    public function configure()
    {
        $this->setRegex('/(?:jemy|zamawiamy) (.+)/iu');
    }

    public function execute(string $message, User $user, Channel $channel)
    {
        parent::execute($message, $user, $channel);

        $this->initializeOrder($this->getPart(1));

        return true;
    }

    protected function initializeOrder(string $restaurant)
    {
        $command = new InitializeOrder\Command($restaurant);

        $useCase = new InitializeOrder(new OrderStorage());
        $useCase->execute($command, $this);
    }

    public function orderInitializedSuccessfully(Order $order)
    {
        $restaurant = $order->getRestaurant();

        $this->advancedReply(function (MessageBuilder $builder) use ($order, $restaurant) {
            $menuItems = [];
            $text = 'OK, zbieram zamówienia do #'. $restaurant->getName();
            $builder->setText('<@' . $this->user->getId() . '> ' . $text);

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
            $builder->addAttachment($attachment);

            return $builder;
        });
    }

    public function orderInitializationFailed(\Exception $e)
    {
        if ($e instanceof NotSupportedRestaurantException) {
            return $this->reply('Nie ma takiej restauracji');
        }

        $this->reply('Coś się zapsuło i nie ma zamawiania :(');
    }
}