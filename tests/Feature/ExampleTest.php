<?php

it('redirects the guest root route to login', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});
