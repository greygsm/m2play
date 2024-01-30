<?php

declare (strict_types=1);
namespace RectorPrefix202304\Doctrine\Inflector\Rules\English;

use RectorPrefix202304\Doctrine\Inflector\Rules\Patterns;
use RectorPrefix202304\Doctrine\Inflector\Rules\Ruleset;
use RectorPrefix202304\Doctrine\Inflector\Rules\Substitutions;
use RectorPrefix202304\Doctrine\Inflector\Rules\Transformations;
final class Rules
{
    public static function getSingularRuleset() : Ruleset
    {
        return new Ruleset(new Transformations(...Inflectible::getSingular()), new Patterns(...Uninflected::getSingular()), (new Substitutions(...Inflectible::getIrregular()))->getFlippedSubstitutions());
    }
    public static function getPluralRuleset() : Ruleset
    {
        return new Ruleset(new Transformations(...Inflectible::getPlural()), new Patterns(...Uninflected::getPlural()), new Substitutions(...Inflectible::getIrregular()));
    }
}
