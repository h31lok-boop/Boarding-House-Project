<?php

test('landing page is explicitly presented to DSSC students', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Exclusively for DSSC students')
        ->assertSee('Davao del Sur State College students')
        ->assertSee('Create Student Account')
        ->assertSee('Register as Owner')
        ->assertDontSee('For owners');
});

test('landing page student registration links preselect the tenant role', function () {
    $this->get('/register?role=tenant')
        ->assertOk()
        ->assertSee('name="role" type="hidden" value="tenant"', false);
});

test('landing header stays focused on sign in while registration actions remain in the page content', function () {
    $response = $this->get('/')->assertOk();
    $html = $response->getContent();
    $header = str($html)->between('<nav class="site-nav"', '</nav>')->toString();
    $callToAction = str($html)->between('<section class="cta"', '</section>')->toString();

    expect($header)
        ->toContain('Sign In')
        ->not->toContain('Register as Owner')
        ->not->toContain('Student Sign Up')
        ->and($callToAction)
        ->toContain('Create Student Account')
        ->toContain('Register as Owner');
});
