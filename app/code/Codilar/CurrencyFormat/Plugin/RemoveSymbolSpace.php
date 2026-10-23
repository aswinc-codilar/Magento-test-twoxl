<?php
/**
 * Removes the space the number formatter puts after the UAE Dirham sign ("⃃ 3,575.00" -> "⃃3,575.00").
 *
 * ICU doesn't know the new sign (U+20C3) yet, so it treats it as a non-symbol and adds currency spacing.
 */
declare(strict_types=1);

namespace Codilar\CurrencyFormat\Plugin;

use Magento\Directory\Model\Currency;
use Magento\Framework\Locale\Format;

class RemoveSymbolSpace
{
    private const DIRHAM_SIGN = "\u{20C3}";

    /**
     * Matches the sign followed by any whitespace, including non-breaking spaces
     */
    private const PATTERN = '/' . self::DIRHAM_SIGN . '[\s\x{00A0}\x{202F}]+/u';

    /**
     * Fix prices formatted on the server
     *
     * @param Currency $subject
     * @param string $result
     * @return string
     */
    public function afterFormatTxt(Currency $subject, $result)
    {
        return is_string($result) ? $this->removeSpace($result) : $result;
    }

    /**
     * Fix the pattern JavaScript uses to format prices (e.g. "⃃ %s")
     *
     * @param Format $subject
     * @param array $result
     * @return array
     */
    public function afterGetPriceFormat(Format $subject, $result)
    {
        if (is_array($result) && isset($result['pattern']) && is_string($result['pattern'])) {
            $result['pattern'] = $this->removeSpace($result['pattern']);
        }

        return $result;
    }

    /**
     * Remove whitespace directly after the dirham sign
     *
     * @param string $text
     * @return string
     */
    private function removeSpace(string $text): string
    {
        if (strpos($text, self::DIRHAM_SIGN) === false) {
            return $text;
        }

        return preg_replace(self::PATTERN, self::DIRHAM_SIGN, $text) ?? $text;
    }
}
