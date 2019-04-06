<?php
namespace DesignForm;
class BAFormInit
{
	public static function get_services() 
	{
		return [
			Base\EnQueue::class,
			Core\BAFormCoreBase::class,
			Core\BAFormFunctionCallBack::class,
			Core\BAFormProfileRegister::class,
			Core\BAFormAddFieldCustom::class,
			Core\Enpoint\BAFormEnpoint::class,
		];
	}
	public static function ba_register_services() 
	{
		foreach ( self::get_services() as $class ) {
			$service = self::instantiate( $class );
			if ( method_exists( $service, 'baform_register_enqueue' ) ) {
				$service->baform_register_enqueue();
			}
			if ( method_exists( $service, 'baform_register_menu' ) ) {
				$service->baform_register_menu();
			}
			if ( method_exists( $service, 'baform_action_construct' ) ) {
				$service->baform_action_construct();
			}
			if ( method_exists( $service, 'baform_run_function_profile' ) ) {
				$service->baform_run_function_profile();
			}
			if ( method_exists( $service, 'baform_run_add_field_custom' ) ) {
				$service->baform_run_add_field_custom();
			}
			if ( method_exists( $service, 'baform_enpoint_run' ) ) {
				$service->baform_enpoint_run();
			}
		}
	}
	private static function instantiate( $class )
	{
		$service = new $class();
		return $service;
	}
}