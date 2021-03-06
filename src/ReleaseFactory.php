<?php
declare(strict_types=1);

namespace Composer\Itineris\WordPress;

use Composer\Itineris\WordPress\Requirement\RequirementCollection;
use Composer\Itineris\WordPress\Util\Url;
use Composer\Util\RemoteFilesystem;

class ReleaseFactory
{
    /** @var RequirementCollection */
    protected $requirementCollection;
    /** @var RemoteFilesystem */
    protected $rfs;

    public function __construct(
        RequirementCollection $requirementCollection,
        RemoteFilesystem $rfs
    ) {
        $this->requirementCollection = $requirementCollection;
        $this->rfs = $rfs;
    }

    public static function make(RemoteFilesystem $rfs): self
    {
        return new static(
            RequirementCollection::make(),
            $rfs
        );
    }

    public function build(string $version, string $downloadUrl): ?Release
    {
        $shasum = $this->getShasum($downloadUrl);
        if (null === $shasum) {
            return null;
        }

        return new Release(
            'itinerisltd/wordpress',
            $version,
            [
                'type' => 'zip',
                'url' => $downloadUrl,
                'shasum' => $shasum,
                'mirrors' => $this->getMirrors($downloadUrl),
            ],
            $this->requirementCollection->forWordPressCore($version)
        );
    }

    protected function getShasum(string $url): ?string
    {
        $shasum = $this->rfs->getContents(
            Url::getHost($url),
            $url . '.sha1'
        );

        return is_string($shasum) ? $shasum : null;
    }

    protected function getMirrors(string $downloadUrl): ?array
    {
        $mirror = str_replace(
            'https://wordpress.org/wordpress-',
            'https://downloads.wordpress.org/release/wordpress-',
            $downloadUrl
        );

        return $downloadUrl === $mirror ? null : [$mirror];
    }
}
