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
        $locale_info = localeconv();
        $decimalPoint = $locale_info['mon_decimal_point'];

        return (null === $amount) ? '0'.$decimalPoint.'00' : substr_replace( $amount, $decimalPoint, -2 , 0);
    }
}
