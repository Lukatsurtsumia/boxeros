<?php

namespace App\Support;

/**
 * A built-in calorie lookup for common foods (typical serving), based on standard nutrition values.
 * Checked BEFORE the AI so recognised meals cost nothing — the AI estimate is only a fallback for
 * foods we don't have. Portions vary, so the result is a sensible baseline the fighter confirms/adjusts.
 *
 * Each entry: label => [kcal per typical serving, [match keywords]].
 * Multi-word / specific items are listed first so they match before their generic parent
 * (e.g. "sweet potato" before "potato", "fried rice" before "rice").
 */
class FoodDb
{
    private const FOODS = [
        // ── prepared / specific (match first) ──
        'cheeseburger'   => [400, ['cheeseburger', 'hamburger']],
        'fried chicken'  => [320, ['fried chicken']],
        'fried rice'     => [350, ['fried rice']],
        'sweet potato'   => [115, ['sweet potato']],
        'peanut butter'  => [190, ['peanut butter']],
        'protein shake'  => [160, ['protein shake', 'protein powder', 'whey', 'mass gainer']],
        'protein bar'    => [200, ['protein bar']],
        'greek yogurt'   => [130, ['greek yogurt', 'greek yoghurt']],
        'cottage cheese' => [160, ['cottage cheese']],
        'scrambled eggs' => [200, ['scrambled egg']],
        'boiled egg'     => [80,  ['boiled egg', 'egg x2', 'two eggs', '2 eggs']],

        // ── proteins ──
        'chicken'  => [250, ['chicken']],
        'beef'     => [300, ['beef', 'steak', 'mince']],
        'salmon'   => [280, ['salmon']],
        'tuna'     => [180, ['tuna']],
        'turkey'   => [200, ['turkey']],
        'pork'     => [250, ['pork', 'pork chop']],
        'lamb'     => [290, ['lamb']],
        'shrimp'   => [100, ['shrimp', 'prawn']],
        'tofu'     => [150, ['tofu']],
        'bacon'    => [180, ['bacon']],
        'sausage'  => [250, ['sausage']],
        'ham'      => [150, ['ham']],
        'eggs'     => [156, ['eggs', 'egg', 'omelette', 'omelet']],

        // ── carbs / grains ──
        'pasta'    => [220, ['pasta', 'spaghetti', 'penne', 'macaroni']],
        'noodles'  => [220, ['noodles', 'ramen']],
        'rice'     => [205, ['rice']],
        'quinoa'   => [220, ['quinoa']],
        'couscous' => [180, ['couscous']],
        'potato'   => [160, ['potato', 'potatoes', 'mashed potato']],
        'oats'     => [150, ['oats', 'oatmeal', 'porridge']],
        'cereal'   => [200, ['cereal', 'granola', 'muesli']],
        'bagel'    => [250, ['bagel']],
        'toast'    => [80,  ['toast']],
        'bread'    => [160, ['bread', 'sandwich', 'roll', 'wrap', 'tortilla', 'pita']],
        'pancakes' => [350, ['pancake']],

        // ── fruit ──
        'banana'   => [105, ['banana']],
        'apple'    => [95,  ['apple']],
        'orange'   => [62,  ['orange']],
        'mango'    => [200, ['mango']],
        'avocado'  => [240, ['avocado']],
        'grapes'   => [100, ['grape']],
        'berries'  => [50,  ['berries', 'blueberry', 'blueberries', 'strawberry', 'strawberries']],

        // ── veg ──
        'broccoli' => [55,  ['broccoli']],
        'spinach'  => [25,  ['spinach']],
        'salad'    => [60,  ['salad', 'lettuce']],
        'veg'      => [60,  ['vegetable', 'veggies', 'greens']],
        'carrot'   => [50,  ['carrot']],
        'tomato'   => [25,  ['tomato']],

        // ── dairy / fats ──
        'milk'     => [120, ['milk']],
        'cheese'   => [110, ['cheese']],
        'butter'   => [100, ['butter']],
        'olive oil'=> [120, ['olive oil', 'oil']],
        'nuts'     => [160, ['nuts', 'almond', 'peanut', 'cashew', 'walnut']],
        'yogurt'   => [150, ['yogurt', 'yoghurt']],
        'honey'    => [60,  ['honey']],

        // ── treats / drinks / snacks ──
        'tiramisu' => [400, ['tiramisu']],
        'ice cream'=> [270, ['ice cream', 'gelato']],
        'cake'     => [350, ['cake', 'brownie']],
        'chocolate'=> [230, ['chocolate']],
        'cookie'   => [150, ['cookie', 'biscuit']],
        'chips'    => [150, ['chips', 'crisps', 'fries']],
        'pizza'    => [570, ['pizza']],
        'burger'   => [350, ['burger']],
        'sushi'    => [350, ['sushi']],
        'kebab'    => [500, ['kebab', 'shawarma', 'doner']],
        'curry'    => [450, ['curry']],
        'soup'     => [150, ['soup']],
        'smoothie' => [250, ['smoothie']],
        'soda'     => [140, ['soda', 'coke', 'cola', 'sprite', 'fanta']],
        'juice'    => [110, ['juice']],
        'beer'     => [150, ['beer']],
        'wine'     => [125, ['wine']],
        'coffee'   => [5,   ['black coffee', 'espresso', 'coffee']],
    ];

    /**
     * Estimate calories for a meal description from the local table.
     * Returns the summed calories of every distinct food found, or null if nothing matched.
     */
    public static function estimate(string $text): ?int
    {
        $t = ' ' . mb_strtolower(trim($text)) . ' ';
        if ($t === '  ') return null;

        $total   = 0;
        $matched = false;

        foreach (self::FOODS as [$kcal, $keywords]) {
            foreach ($keywords as $kw) {
                if (str_contains($t, $kw)) {
                    $total  += $kcal;
                    $matched = true;
                    $t       = str_replace($kw, ' ', $t); // consume so the same food isn't counted twice
                    break;
                }
            }
        }

        return $matched ? $total : null;
    }
}
