<?php

namespace MF\Model;

use LM\WebFramework\Model\Type\EntityModel;

/**
 * Use this class to replace all model-specific factories?
 */
class ModelFactory
{
    public function __construct(
        private ArticleModelFactory $articleModelFactory,
        private AuthorModelFactory $authorModelFactory,
        private BookModelFactory $bookModelFactory,
        private CategoryModelFactory $categoryModelFactory,
        private ChapterIndexModelFactory $chapterIndexModelFactory,
        private ChapterModelFactory $chapterModelFactory,
        private ContributionModelFactory $contributionModelFactory,
        private MemberModelFactory $memberModelFactory,
        private PlayableLinkModelFactory $playableLinkModelFactory,
        private PlayableModelFactory $playableModelFactory,
        private ReviewModelFactory $reviewModelFactory,
    ) {
    }

    public function getArticleModel(
        bool $author = false,
        bool $category = true,
        bool $review = false,
    ): EntityModel
    {
        $model = $this->articleModelFactory->create(
            categoryModel: $category ? $this->getCategoryModel() : null,
            reviewModel: $review ? $this->getReviewModel(playable: true) : null,
        );

        return $model;
    }

    public function getArticleModelFull(): EntityModel
    {
        $reviewModel = $this->reviewModelFactory->create(
            playableModel: $this->getPlayableModel(contributions: true, game: true, links: true),
        );
        $model = $this->articleModelFactory->create(
            authorModel: $this->getAuthorModel()->setIdentifier('writer')->removeProperty('avatar_filename'),
            categoryModel: $this->getCategoryModel(),
            reviewModel: $reviewModel,
        );

        return $model;
    }

    public function getAuthorModel(): EntityModel
    {
        return $this->authorModelFactory->create();
    }

    public function getBookModel(): EntityModel
    {
        return $this->bookModelFactory->create();
    }

    public function getCategoryModel(): EntityModel
    {
        $model = $this->categoryModelFactory->create();
        $model->addItselfAsProperty('parent', 'id', 'parent_id', true);
        return $model;
    }

    public function getChapterModel(bool $withBookModel = true): EntityModel
    {
        return $this->chapterModelFactory->create(bookModel: $withBookModel ? $this->getBookModel() : null);
    }

    public function getChapterIndexModel(): EntityModel
    {
        return $this->chapterIndexModelFactory->create();
    }

    public function getContributionModel(bool $author = true): EntityModel
    {
        return $this->contributionModelFactory->create($author ? $this->getAuthorModel() : null);
    }

    public function getMemberModel(): EntityModel
    {
        return $this->memberModelFactory->create();
    }

    public function getPlayableLinkModel(): EntityModel
    {
        return $this->playableLinkModelFactory->create();
    }

    public function getPlayableModel(
        bool $contributions = false,
        bool $game = false,
        bool $links = false,
        bool $mods = false,
    ): EntityModel {
        $model = $this->playableModelFactory->create(
            $game ? $this->playableModelFactory->create(isGame: true) : null,
            $links ? $this->getPlayableLinkModel() : null,
            $contributions ? $this->getContributionModel() : null,
            $mods ? $this->getPlayableModel() : null,
        );
        return $model;
    }

    public function getReviewModel(
        bool $playable = false,
        bool $gameIfPlayable = true,
    ) : EntityModel {
        $model = $this->reviewModelFactory->create(
            $playable ? $this->getPlayableModel(game: $gameIfPlayable) : null,
        );
        return $model;
    }
}