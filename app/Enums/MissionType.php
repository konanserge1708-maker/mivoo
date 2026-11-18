<?php

namespace App\Enums;

enum MissionType: string
{
    case VisitWebsite = 'visit_website';
    case WatchVideo = 'watch_video';
    case LikePost = 'like_post';

    public function label(): string
    {
        return match ($this) {
            self::VisitWebsite => 'Visite site web',
            self::WatchVideo => 'Lecture de vidéo',
            self::LikePost => 'Like de post',
        };
    }

    public function categorySlug(): string
    {
        return match ($this) {
            self::VisitWebsite => 'visite-site-web',
            self::WatchVideo => 'lecture-de-video',
            self::LikePost => 'like-de-post',
        };
    }

    public function openButtonLabel(): string
    {
        return match ($this) {
            self::VisitWebsite => 'Ouvrir le site',
            self::WatchVideo => 'Ouvrir la vidéo',
            self::LikePost => 'Ouvrir le post',
        };
    }

    public function confirmationLabel(): string
    {
        return match ($this) {
            self::VisitWebsite => 'J’ai visité le site',
            self::WatchVideo => 'J’ai regardé la vidéo',
            self::LikePost => 'J’ai liké le post',
        };
    }

    public function defaultDescription(): string
    {
        return match ($this) {
            self::VisitWebsite => 'Ouvrez le lien, consultez le site, puis confirmez votre visite.',
            self::WatchVideo => 'Ouvrez le lien, regardez la vidéo, puis confirmez.',
            self::LikePost => 'Ouvrez le post, mettez un like, puis confirmez.',
        };
    }

    public function defaultInstructions(): string
    {
        return match ($this) {
            self::VisitWebsite => "1. Cliquez sur « Ouvrir le site »\n2. Parcourez la page quelques secondes\n3. Revenez ici et cochez « J’ai visité le site »\n4. Validez la mission",
            self::WatchVideo => "1. Cliquez sur « Ouvrir la vidéo »\n2. Regardez la vidéo\n3. Revenez ici et cochez « J’ai regardé la vidéo »\n4. Validez la mission",
            self::LikePost => "1. Cliquez sur « Ouvrir le post »\n2. Mettez un like sur le post\n3. Revenez ici et cochez « J’ai liké le post »\n4. Validez la mission",
        };
    }

    public function isConfirmation(): bool
    {
        return true;
    }

    public function badgeTone(): string
    {
        return match ($this) {
            self::WatchVideo => 'red',
            self::LikePost => 'blue',
            self::VisitWebsite => 'dark',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->all();
    }
}
