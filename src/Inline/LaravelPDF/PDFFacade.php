<?php namespace Inline\LaravelPDF;

use Illuminate\Support\Facades\Facade;

class PDFFacade extends Facade
{

  /**
   * Get the registered name of the component.
   *
   * @return string
   */
  protected static function getFacadeAccessor()
  {
    return 'pdf';
  }


}