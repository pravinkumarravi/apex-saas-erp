<?php

test('the application returns a successful response for tenant dashboard', function () {
    $response = $this->get('/?tenant=companya');

    $response->assertStatus(200);
});
