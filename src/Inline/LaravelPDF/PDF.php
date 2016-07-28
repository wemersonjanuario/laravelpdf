<?php namespace Inline\LaravelPDF;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class PDF extends \Inline\PDF\PDF
{
    /**
     * @var array of \Inline\LaravelPDF\PDF for the case of output several pages
     *   to a single PDF file.
     */
    protected $children = array();

    /**
     * Loads the Input Content from the view
     *
     * @param string $viewName
     * @param array $data
     * @param array $mergeData
     *
     * @return $this
     */
    public function loadView($viewName, $data = array(), $mergeData = array())
    {
        $this->htmlContent .= View::make($viewName, $data, $mergeData);

        return $this;
    }

    /**
     * Mass version of the loadView function.
     *
     * @param array $viewNames
     *   Array of Laravel view names to include in resulting page. Order matters.
     * @param array $data
     *   Data array is shared across all views.
     * @param array $mergeData
     *
     * @return $this
     *
     * @see: loadView()
     */
    public function loadViews($viewNames = array(), $data = array(), $mergeData = array())
    {
        $this->children = array();
        foreach ($viewNames as $key => $viewName) {
            $className = get_class();
            $item = new $className($this->cmd, $this->folder);
            $item->loadView($viewName, $data, $mergeData);
            $this->children[] = $item;
        }

        return $this;
    }

    /**
     * Mass loads HTML Content.
     *
     * @param array string $htmls
     *
     * @return $this
     */
    public function loadHTMLs($htmls)
    {
        $this->children = array();
        foreach ($htmls as $key => $html) {
            $className = get_class();
            $item = new $className($this->cmd, $this->folder);
            /* @var \Inline\LaravelPDF\PDF $item */
            $item->loadHTML($html);
            $this->children[] = $item;
        }
        return $this;
    }

    /**
     * Updated version which can handle multiple source files.
     *
     * @return string
     */
    protected function getInputSource()
    {
        if (empty($this->children)) {
            return parent::getInputSource();
        }

        $childPaths = [];
        foreach ($this->children as $child) {
          $childPaths[] = $child->getInputSource();
        }

        return implode(" ", $childPaths);
    }

    /**
     * Get the PDF Content as an attachment
     *
     * @param string $as
     *
     * @return \Illuminate\Http\Response
     */
    public function download($as = null)
    {
        return $this->createResponse()->header('Content-Disposition', 'attachment; ' . $this->getAs($as));
    }

    /**
     * Display the PDF Document in the browser window
     *
     * @param string $as
     *
     * @return \Illuminate\Http\Response
     */
    public function stream($as = null)
    {
        return $this->createResponse()->header('Content-Disposition', 'inline; ' . $this->getAs($as));
    }

    /**
     * Creates a response object with proper Content-type for PDF Doc.
     *
     * @return \Illuminate\Http\Response
     */
    protected function createResponse()
    {
        return Response::make($this->get(), 200)->header('Content-type', 'application/pdf');
    }

    /**
     * Gets the attachment name for the Response
     *
     * @param string $as
     *
     * @return string
     */
    protected function getAs($as = null)
    {
        if (!is_null($as)) {
            return 'filename="' . $as . '"';
        }

        return "";
    }

}