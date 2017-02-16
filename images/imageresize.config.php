<?php
    $templates = [
        'thumbnail' => [
            'type' => 'crop',  # fit,crop
            'quality' => 70,   # jpeg quality %
            'width' => 100,    # image width (or maximum with type 'fit')
            'height' => 100,   # image height (or maximum with type 'fit')
        ],
        'small' => [
            'type' => 'fit',
            'quality' => 70,
            'width' => 600,
            'height' => 600,
        ],
        'medium' => [
            'type' => 'fit',
            'quality' => 70,
            'width' => 1024,
            'height' => 1024,
        ],
        'large' => [
            'type' => 'fit',
            'quality' => 70,
            'width' => 1600,
            'height' => 1600,
        ],
        'blur' => [
            'type' => 'fit',
            'blur' => 30,
            'width' => 300,
            'height' => 200,
        ],
        'gray' => [
            'type' => 'fit',
            'grayscale' => true,
            'width' => 300,
            'height' => 200,
        ],
    ];