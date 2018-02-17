<?php namespace Nekudo\ShinyBlog\Controller\Http;

use Nekudo\ShinyBlog\Domain\ShowFeedDomain;
use Nekudo\ShinyBlog\Exception\NotFoundException;
use Nekudo\ShinyBlog\Responder\ShowFeedResponder;

class ShowFeed extends BaseAction
{
    /** @var ShowFeedDomain $domain */
    protected $domain;

    /** @var ShowFeedResponder $responder */
    protected $responder;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->domain = new ShowFeedDomain($this->config);
        $this->responder = new ShowFeedResponder($this->config);
    }

    /**
     * Renders requested article and sends it to client.
     *
     * @param array $arguments
     */
    public function __invoke(array $arguments)
    {
        try {
            $category = $arguments['slug'] ?? '';
            $limit = $this->config['feed']['limit'];
            $articles = $this->domain->getArticles($limit, $category);
            if (!empty($articles)) {
                $pubDate = date(DATE_RSS, strtotime($articles[0]->getDate()));
                $this->responder->setChannelPubDate($pubDate);
            }
            $this->responder->setChannelTitle($this->config['seo']['blog']['title']);
            $this->responder->setChannelDescription($this->config['seo']['blog']['description']);
            $this->responder->setChannelLink($this->domain->getFeedUrlPath($category));
            $this->responder->addArticles($articles);
            $this->responder->__invoke();
            return $this->responder;
        } catch (NotFoundException $e) {
            $this->responder->notFound($e->getMessage());
            return $this->responder;
        }
    }
}
