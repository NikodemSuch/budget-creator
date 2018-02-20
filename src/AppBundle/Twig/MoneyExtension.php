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

    public function moneyFilter($amount, $locale)
    {
        $fmt = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
        $decimalPoint = $fmt->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);

        return (null == $amount) ? "0${decimalPoint}00" : substr_replace($amount, $decimalPoint, -2 , 0);
    }
}
