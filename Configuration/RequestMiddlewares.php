<?php
return [
    'frontend' => [
        'output-compress-html' => [
            'target' => \Npostnik\Min\Middleware\HtmlCompress::class,
            'before' => [
                'typo3/cms-frontend/output-compression'
            ],
            'after' => [
                'typo3/cms-adminpanel/renderer'
            ]
        ]
    ]
];
