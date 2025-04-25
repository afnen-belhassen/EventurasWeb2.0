<?php

namespace App\Enum;

enum ContractType: string
{
    case STANDARD = 'standard';
    case PREMIUM = 'premium';
    case EXCLUSIVE = 'exclusive';
    case SPONSORSHIP = 'sponsorship';
    case MEDIA = 'media';
    case TECHNICAL = 'technical';
    case LOGISTICS = 'logistics';
    case VENUE = 'venue';
    
    public function getLabel(): string
    {
        return match($this) {
            self::STANDARD => 'Standard',
            self::PREMIUM => 'Premium',
            self::EXCLUSIVE => 'Exclusif',
            self::SPONSORSHIP => 'Sponsoring',
            self::MEDIA => 'Média',
            self::TECHNICAL => 'Technique',
            self::LOGISTICS => 'Logistique',
            self::VENUE => 'Lieu',
        };
    }
    
    public static function getChoices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->getLabel()] = $case->value;
        }
        return $choices;
    }
} 