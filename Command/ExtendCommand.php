<?php
/**
 * @author Aaron Scherer <aequasi@gmail.com>
 * @date Oct 12, 2012
 */
namespace Uecode\DaemonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Uecode\DaemonBundle\System\Daemon\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use \Uecode\DaemonBundle\Service\DaemonService;

/**
 * Extendable Command class
 */
abstract class ExtendCommand extends ContainerAwareCommand
{
	/**
	 * @var DaemonService $daemon
	 */
	protected $daemon;

	/**
	 * @var Container
	 */
	protected $container;

	protected function configure()
	{
		throw new \Exception( sprintf( 'You must implement a `%s` method for your class.', 'configure' ) );
	}

	/**
	 * Grabs the argument data and runs the argument on the daemon
	 * @param \Symfony\Component\Console\Input\InputInterface   $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return void
	 * @throws \Exception
	 */
	protected function execute( InputInterface $input, OutputInterface $output )
	{
		$method = $input->getArgument( 'method' );
		if ( !in_array( $method, array( 'start', 'stop', 'restart' ) ) ) {
			throw new \Exception( 'Method must be `start`, `stop`, or `restart`' );
		}
		$this->container = $this->getContainer();

		$this->createDaemon( );
		$this->$method( $input, $output );
	}

	/**
	 * Creates and Initializes the daemon
	 */
	final protected function createDaemon( )
	{
		$this->daemon = $this->container->get( 'uecode.daemon' );
		$this->daemon->initialize( $this->container->getParameter( 'spawner.daemon.options' ) );
	}

	/**
	 * Starts the Daemon
	 * @param \Symfony\Component\Console\Input\InputInterface   $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @throws \Uecode\DaemonBundle\System\Daemon\Exception
	 */
	final protected function start( InputInterface $input, OutputInterface $output )
	{
		if( $this->daemon->isRunning() )
		{
			throw new Exception( 'Daemon is already running!' );
		}

		$this->daemon->start();
		while ( $this->daemon->isRunning() ) {
			// Do stuff here
			$this->daemonLogic( $input, $output );
		}
		$this->daemon->stop();

	}

	/**
	 * Restarts the Daemon
	 * @param \Symfony\Component\Console\Input\InputInterface   $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @throws \Uecode\DaemonBundle\System\Daemon\Exception
	 */
	final protected function restart( InputInterface $input, OutputInterface $output )
	{
		if( !$this->daemon->isRunning() )
		{
			throw new Exception( 'Daemon is not running!' );
		}

		$this->daemon->restart();
		while ( $this->daemon->isRunning() ) {
			// Do stuff here
			$this->daemonLogic( $input, $output );
		}
		$this->daemon->stop();
	}

	/**
	 * Stops the Daemon
	 * @param \Symfony\Component\Console\Input\InputInterface   $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @throws \Uecode\DaemonBundle\System\Daemon\Exception
	 */
	final protected function stop( InputInterface $input, OutputInterface $output )
	{
		if( !$this->daemon->isRunning() )
		{
			throw new Exception( 'Daemon is not running!' );
		}
		$this->daemon->stop();
	}

	/**
	 * Sample Daemon Logic. Logs `Daemon is running!` every 5 seconds
	 * @param \Symfony\Component\Console\Input\InputInterface   $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 */
	abstract protected function daemonLogic( InputInterface $input, OutputInterface $output );
}