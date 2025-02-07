<?php

use Livewire\Volt\Volt;

Volt::route('/','home')->name('home');

Volt::route('/source-code-generator/', 'source-code-generator')->name('source-code-generator');

Volt::route('/formal-model-generator/', 'formal-model-generator')->name('formal-model-generator');

Volt::route('/code-validation/', 'code-validation')->name('code-validation');

Volt::route('/feedback', 'feedback')->name('feedback');

Volt::route('/settings', 'settings')->name('settings');

Volt::route('/logs', 'logs')->name('logs');
