<?php

namespace MF\Controller;

use LM\WebFramework\Controller\IController;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Model\Type\IModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface IFormController extends IController
{
    public function getFormConfig(): array;

    public function getFormModel(): IModel;

    public function respondToDeletion(string $entityId): ResponseInterface;

    public function respondToInsertion(AppObject $entity): ResponseInterface;

    public function respondToUpdate(AppObject $entity, string $persistedId): ResponseInterface;

    public function respondToNonPersistedRequest(ServerRequestInterface $request, ?array $formData, ?array $formErrors, ?array $deleteFormErrors): ResponseInterface;

    public function getUniqueConstraintFailureMessage(): string;

    public function prepareFormData(ServerRequestInterface $request, array $formData): array;
}