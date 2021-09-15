<?php
/**
 * The manifest of files that are local to specific environment.
 * This file returns a list of environments that the application
 * may be installed under. The returned data must be in the following
 * format:
 *
 * ```php
 * return [
 *     'environment name' => [
 *         'path' => 'directory storing the local files',
 *         'skipFiles'  => [
 *             // list of files that should only copied once and skipped if they already exist
 *         ],
 *         'setWritable' => [
 *             // list of directories that should be set writable
 *         ],
 *         'setExecutable' => [
 *             // list of files that should be set executable
 *         ],
 *         'setCookieValidationKey' => [
 *             // list of config files that need to be inserted with automatically generated cookie validation keys
 *         ],
 *         'createSymlink' => [
 *             // list of symlinks to be created. Keys are symlinks, and values are the targets.
 *         ],
 *     ],
 * ];
 * ```
 */
return [
    'Development' => [
        'path' => 'dev',
        'setWritable' => [
            'manage/runtime',
            'manage/web/assets',
            'home/runtime',
            'home/web/assets',
            'common/entity/nodes',
            'manage/modules/prototype/views/node',
            'manage/modules/prototype/views/form',
            'uploads/files',
            'uploads/images',
            'uploads/temps',
            'uploads/imports',
        ],
        'setExecutable' => [
            'yii',
            'tests/codeception/bin/yii',
        ],
        'setCookieValidationKey' => [
            'manage/config/main-local.php',
            'home/config/main-local.php',
        ],
    ],
    'Production' => [
        'path' => 'prod',
        'setWritable' => [
            'manage/runtime',
            'manage/web/assets',
            'home/runtime',
            'home/web/assets',
            'uploads/files',
            'uploads/images',
            'uploads/temps',
            'uploads/imports',
        ],
        'setExecutable' => [
            'yii',
            'common/entity/nodes',
            'manage/modules/prototype/views/node',
            'manage/modules/prototype/views/form',
        ],
        'setCookieValidationKey' => [
            'manage/config/main-local.php',
            'home/config/main-local.php',
        ],
    ],
];
