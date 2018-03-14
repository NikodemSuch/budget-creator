<?php

namespace AppBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;

class MoneyExtension extends \Twig_Extension
{
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('money', [$this, 'moneyFilter']),
        );
    }

    public function moneyFilter($amount)
    {
        $request = $this->requestStack->getCurrentRequest();
        $locale = $request->getLocale();
        $fmt = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
        $decimalPoint = $fmt->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);

        if (!$amount) {
            return "0${decimalPoint}00";
        }

        $amountStr = str_pad($amount, 3, '0', STR_PAD_LEFT);
        return substr_replace($amountStr, $decimalPoint, -2 , 0);
    }
}
