<?php

return [

    'label' => 'Set up',

    'modal' => [

        'heading' => 'Email verification codes siam na',

        'description' => 'Email hmanga 6-digit code kan rawn thawn kha I sign in dawnah emaw thil sensitive deuh hlek tih dawnah i chhutluh zel a ngai ang. Setup puitling turin 6-digit code i email ah en rawh.',

        'form' => [

            'code' => [

                'label' => '6-digit code email hmanga kan rawn thawn kha enter rawh',

                'validation_attribute' => 'code',

                'actions' => [

                    'resend' => [

                        'label' => 'Email ah code thar thawn rawh',

                        'notifications' => [

                            'resent' => [
                                'title' => 'Email hmangin code thar kan rawn thawn e',
                            ],

                        ],

                    ],

                ],

                'messages' => [

                    'invalid' => 'Hemi code hi a diklo.',

                ],

            ],

        ],

        'actions' => [

            'submit' => [
                'label' => 'mail verification codes enable rawh',
            ],

        ],

    ],

    'notifications' => [

        'enabled' => [
            'title' => 'Email verification codes chu enabled a ni e',
        ],

    ],

];
