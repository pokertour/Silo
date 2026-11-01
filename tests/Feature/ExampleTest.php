<?php

test('the application redirects the root to the dashboard', function () {
    $this->get('/')->assertRedirect('/dashboard');
});
