<?php

namespace AppBundle\Twig;

class MoneyExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('money', [$this, 'moneyFilter']),
        );
    }

    public function moneyFilter($amount)
    {
        return (null === $amount) ? '0,00' : (string) substr_replace($amount, ',', strlen($amount) - 2 , 0);
    }
}
