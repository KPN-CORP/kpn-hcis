<?php

test('goals_contains_empty_table', function () {
    $response = $this->get('/goals');

    $response->assertStatus(200);
    $response->assertSee(__(key: 'No Goals Found'));
});