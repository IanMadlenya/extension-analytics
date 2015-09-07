<?php

return [

    'uninstall' => function ($app) {
        $app['config']->remove($this->name);
    }

];