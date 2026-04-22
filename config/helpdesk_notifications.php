<?php

return [
    'telegram' => [
        'ticket_created_template' => env(
            'TELEGRAM_TICKET_CREATED_TEMPLATE',
            "Tiket baru masuk\nNomor: {ticket_number}\nJudul: {title}\nPrioritas: {priority}\nKategori: {category}\nPelapor: {reporter}\nLink: {ticket_url}"
        ),
    ],
];
