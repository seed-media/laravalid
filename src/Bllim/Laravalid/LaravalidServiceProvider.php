<?php namespace Bllim\Laravalid;

use Illuminate\Support\ServiceProvider;

class LaravalidServiceProvider extends ServiceProvider {

	/**
	 * {@inheritdoc}
	 */
	protected $defer = false;

	/**
	 * {@inheritdoc}
	 */
	public function boot()
	{
		$this->package('bllim/laravalid', 'laravalid');

		// register routes for `remote` validations
		$app = $this->app;
		$routeName = $app['config']->get('laravalid::route');

		$app['router']->any($routeName . '/{rule}', function ($rule) use ($app) {
			return $app['laravalid']->converter()->route()->convert($rule, $app['request']->all());
		})->where('rule', '[\w-]+');
	}

	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		// try to register the HTML builder instance
		if (!$this->app->bound('html')) {
			$this->app->bindShared('html', function($app)
			{
				return new \Illuminate\Html\HtmlBuilder($app['url']);
			});
		}

		// register the new form builder instance
		$this->app->bindShared('laravalid', function ($app) {
			/* @var $app \Illuminate\Foundation\Application */
			$plugin = $app['config']->get('laravalid::plugin');
			$converterClass = (strpos($plugin, '\\') === false ? 'Bllim\Laravalid\Converter\\' : '') . $plugin . '\Converter';

			$session = $app['session.store'];
			$form = new FormBuilder($app['html'], $app['url'], $session->getToken(), new $converterClass);

			return $form->setSessionStore($session);
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function provides()
	{
		return array('laravalid');
	}

}
