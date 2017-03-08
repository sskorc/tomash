<?php

namespace Application\AppBundle\Slack\Command;

use Spatie\Regex\MatchResult;
use Spatie\Regex\Regex;

abstract class AbstractCommand
{
    /** @var string */
    protected $regexPattern;

    /** @var MatchResult */
    protected $regex;

    public function setRegex(string $regex)
    {
        $this->regexPattern = $regex;
    }

    public function matches(string $message) : bool
    {
        $this->regex = Regex::match($this->regexPattern, $message);

        return $this->regex->hasMatch();
    }

    public function getPart(int $index) : string
    {
        return $this->regex->group($index);
    }

    protected function getPeriod(string $date)
    {
        $date = trim($date);

        $startDate = null;
        $endDate = null;

        if ($date === 'jutro') {
            $startDate = new \DateTime('+1 day');
        }

        if ($date === 'dziś' || $date === 'dzis' || $date === 'dzisiaj') {
            $startDate = new \DateTime('now');
        }

        $datesRegex = Regex::match('/(\d{1,2}).(\d{1,2})-(\d{1,2}).(\d{1,2})/', $date);
        if ($datesRegex->hasMatch()) {
            $dayStart = $datesRegex->group(1);
            $monthStart = $datesRegex->group(2);
            $dayEnd = $datesRegex->group(3);
            $monthEnd = $datesRegex->group(4);

            $startDate = new \DateTime($monthStart . '/' . $dayStart);
            $endDate = new \DateTime($monthEnd . '/' . $dayEnd);
        }

        $datesRegex = Regex::match('/(\d{1,2}).(\d{1,2})/', $date);
        if ($datesRegex->hasMatch()) {
            $dayStart = $datesRegex->group(1);
            $monthStart = $datesRegex->group(2);

            $startDate = new \DateTime($monthStart . '/' . $dayStart);
        }

        if (empty($endDate)) {
            $endDate = $startDate;
        }

        return ['startDate' => $startDate, 'endDate' => $endDate];
    }
}
