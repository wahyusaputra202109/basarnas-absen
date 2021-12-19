<?php

Route::get('/{any}', 'AppCtrl@index')->where('any', '.*');
