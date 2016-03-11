<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Sean Tymon <tymon148@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tymon\JWTAuth\Test\Middleware;

use Mockery;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Http\Parser;
use Tymon\JWTAuth\Test\Stubs\UserStub;
use Tymon\JWTAuth\Test\AbstractTestCase;
use Tymon\JWTAuth\Http\Middleware\Authenticate;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthenticateTest extends AbstractTestCase
{
    /**
     * @var \Mockery\MockInterface|\Tymon\JWTAuth\JWTAuth
     */
    protected $auth;

    /**
     * @var \Mockery\MockInterface
     */
    protected $request;

    /**
     * @var \Tymon\JWTAuth\Http\Middleware\Authenticate
     */
    protected $middleware;

    public function setUp()
    {
        parent::setUp();

        $this->auth = Mockery::mock(JWTAuth::class);
        $this->request = Mockery::mock(Request::class);

        $this->middleware = new Authenticate($this->auth);
    }

    public function tearDown()
    {
        Mockery::close();

        parent::tearDown();
    }

    /** @test */
    public function it_should_authenticate_a_user()
    {
        $parser = Mockery::mock(Parser::class);
        $parser->shouldReceive('hasToken')->once()->andReturn(true);

        $this->auth->shouldReceive('parser')->andReturn($parser);

        $this->auth->parser()->shouldReceive('setRequest')->once()->with($this->request)->andReturn($this->auth->parser());
        $this->auth->shouldReceive('parseToken->authenticate')->once()->andReturn(new UserStub);

        $this->middleware->handle($this->request, function () {});
    }

    /**
     * @test
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function it_should_throw_a_bad_request_exception_if_token_not_provided()
    {
        $parser = Mockery::mock(Parser::class);
        $parser->shouldReceive('hasToken')->once()->andReturn(false);

        $this->auth->shouldReceive('parser')->andReturn($parser);
        $this->auth->parser()->shouldReceive('setRequest')->once()->with($this->request)->andReturn($this->auth->parser());

        $this->middleware->handle($this->request, function () {});
    }

    /**
     * @test
     * @expectedException \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     */
    public function it_should_throw_an_unauthorized_exception_if_token_invalid()
    {
        $parser = Mockery::mock(Parser::class);
        $parser->shouldReceive('hasToken')->once()->andReturn(true);

        $this->auth->shouldReceive('parser')->andReturn($parser);

        $this->auth->parser()->shouldReceive('setRequest')->once()->with($this->request)->andReturn($this->auth->parser());
        $this->auth->shouldReceive('parseToken->authenticate')->once()->andThrow(new TokenInvalidException);

        $this->middleware->handle($this->request, function () {});
    }
}
