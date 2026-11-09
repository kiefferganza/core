<?php

test('homepage returns successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
