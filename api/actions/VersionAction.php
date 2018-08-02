<?php

namespace api\actions;

use yrc\rest\Action;
use yii\helpers\Json;

/**
 * @class VersionAction
 * Returns the version string for the correct environment
 */
class VersionAction extends Action
{
    /**
     * @param array $params
     */
    public function get(array $params = [])
    {
        $data = [];
        $versionFile = ROOT . '/VERSION';
        $gitHead = ROOT . '/.git/HEAD';
        if (\file_exists($versionFile)) {
            $data = Json::decode(\file_get_contents(ROOT . '/VERSION'));
        } elseif (\file_exists($gitHead)) {
            $gitRevision = \file_get_contents($gitHead);
            $path = \explode('/', $gitRevision);
            $data = [
                'build' => \str_replace("\n", '', 'dev-' . $path[2]),
                'date' => date('D M d h:i:s T Y', \filemtime($gitHead))
            ];
        } else {
            $data = [
                'build' => 'unknown',
                'date' => date('D M d h:i:s T Y')
            ];
        }

        return $data;
    }
}
