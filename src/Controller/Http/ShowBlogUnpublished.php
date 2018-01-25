<?php namespace Nekudo\ShinyBlog\Controller\Http;

use Nekudo\ShinyBlog\Domain\ShowBlogDomain;

class ShowBlogUnpublished extends BaseAction
{
    protected $domain;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->domain = new ShowBlogDomain($this->config);
    }

    public function __invoke(array $arguments)
    {
        $not_published = $this->domain->getUnpublishedArticles();

        echo "<h1 style=\"font-family: Arial, Helvetica, sans-serif;\">The Unpublished Ones</h1>";
        echo "<ol>";
        foreach ($not_published as $article) {
            echo '<li><a style="font-family: Arial, Helvetica, sans-serif;" href="'.$article->getUrl().'">'.$article->getTitle()."</a></li>";
        }
        echo "</ol>";
    }
}
