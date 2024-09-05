<?php

namespace MF\Controller;

use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Model\Type\IModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface IFormController extends ControllerInterface
{
    public function getFormConfig(): array;

    public function getFormModel(): IModel;

    public function respondToDeletion(string $entityId): ResponseInterface;

    public function respondToInsertion(AppObject $entity): ResponseInterface;

    public function respondToUpdate(AppObject $entity, string $previousId): ResponseInterface;

    public function respondToNonPersistedRequest(?array $formData, ?array $formErrors, ?array $deleteFormErrors, ?string $id): ResponseInterface;

    public function getUniqueConstraintFailureMessage(): string;

    public function prepareFormData(ServerRequestInterface $request, array $formData): array;
}