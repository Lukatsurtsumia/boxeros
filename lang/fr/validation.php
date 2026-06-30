<?php

// Common rules used by the auth forms (register/login/reset). Anything not listed
// falls back to the English messages via config('app.fallback_locale').

return [
    'required'  => 'Le champ :attribute est obligatoire.',
    'email'     => 'Le champ :attribute doit être une adresse e-mail valide.',
    'unique'    => 'Cette adresse :attribute est déjà utilisée.',
    'confirmed' => 'La confirmation du :attribute ne correspond pas.',
    'string'    => 'Le champ :attribute doit être une chaîne de caractères.',
    'min' => [
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'max' => [
        'string' => 'Le champ :attribute ne peut pas dépasser :max caractères.',
    ],

    'attributes' => [
        'email'    => 'e-mail',
        'password' => 'mot de passe',
        'name'     => 'nom',
    ],
];
