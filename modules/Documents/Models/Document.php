<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.3.1
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2023 KONKORD DIGITAL
 */

namespace Modules\Documents\Models;

use Barryvdh\DomPDF\Facade\Pdf;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Billable\Concerns\HasProducts;
use Modules\Core\Concerns\HasCreator;
use Modules\Core\Concerns\HasUuid;
use Modules\Core\Concerns\Prunable;
use Modules\Core\Contracts\Presentable;
use Modules\Core\Facades\ChangeLogger;
use Modules\Core\Models\Model;
use Modules\Core\Resource\Resourceable;
use Modules\Core\Resource\ResourcePlaceholders;
use Modules\Core\Support\Changelog\LogsModelChanges;
use Modules\Core\Support\Date\Carbon;
use Modules\Core\Support\Media\HasAttributesWithPendingMedia;
use Modules\Core\Support\Placeholders\DatePlaceholder;
use Modules\Core\Support\Placeholders\GenericPlaceholder;
use Modules\Core\Support\Placeholders\Placeholders as BasePlaceholders;
use Modules\Core\Support\Timeline\Timelineable;
use Modules\Core\Workflow\HasWorkflowTriggers;
use Modules\Documents\Content\DocumentContent;
use Modules\Documents\Content\FontsExtractor;
use Modules\Documents\Database\Factories\DocumentFactory;
use Modules\Documents\Enums\DocumentStatus;
use Modules\Documents\Enums\DocumentViewType;
use Modules\Users\Models\User;

/**
 * @property-read \Modules\Documents\Content\DocumentContent $content
 */
class Document extends Model implements Presentable, Viewable
{
    use HasAttributesWithPendingMedia,
        HasCreator,
        HasFactory,
        HasProducts,
        HasUuid,
        HasWorkflowTriggers,
        InteractsWithViews,
        LogsModelChanges,
        Prunable,
        Resourceable,
        SoftDeletes,
        Timelineable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'requires_signature',
        'content',
        'data',
        'brand_id',
        'user_id',
        'document_type_id',
        'send_at',
        'view_type',
        'locale',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'int',
        'created_by' => 'int',
        'marked_accepted_by' => 'int',
        'document_type_id' => 'int',
        'brand_id' => 'int',
        'owner_assigned_date' => 'datetime',
        'accepted_at' => 'datetime',
        'original_date_sent' => 'datetime',
        'last_date_sent' => 'datetime',
        'send_at' => 'datetime',
        'requires_signature' => 'bool',
        'status' => DocumentStatus::class,
        'data' => 'array',
        'view_type' => DocumentViewType::class,
    ];

    /**
     * The columns for the model that are searchable.
     */
    protected static array $searchableColumns = [
        'title' => 'like',
        'document_type_id',
        'status',
        'amount',
        'brand_id',
        'sent_by',
        'user_id',
        'created_by',
    ];

    /**
     * Add new activity
     */
    public function addActivity(array $data): static
    {
        ChangeLogger::onModel($this, $data)->log();

        return $this;
    }

    /**
     * Get the document signers
     */
    public function signers(): HasMany
    {
        return $this->hasMany(\Modules\Documents\Models\DocumentSigner::class);
    }

    /**
     * A document belongs to type
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(\Modules\Documents\Models\DocumentType::class, 'document_type_id');
    }

    /**
     * Get all of the deals that are associated with this document.
     */
    public function deals(): MorphToMany
    {
        return $this->morphedByMany(\Modules\Deals\Models\Deal::class, 'documentable');
    }

    /**
     * Get all of the contacts that are associated with this document.
     */
    public function contacts(): MorphToMany
    {
        return $this->morphedByMany(\Modules\Contacts\Models\Contact::class, 'documentable')
            ->withTimestamps()
            ->orderBy('documentables.created_at');
    }

    /**
     * Get all of the companies that are associated with this document.
     */
    public function companies(): MorphToMany
    {
        return $this->morphedByMany(\Modules\Contacts\Models\Company::class, 'documentable')
            ->withTimestamps()
            ->orderBy('documentables.created_at');
    }

    /**
     * Get the document owner
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Users\Models\User::class);
    }

    /**
     * Get the document brand
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(\Modules\Brands\Models\Brand::class);
    }

    /**
     * Get the document PDF font.
     */
    public function pdfFont(): array
    {
        $family = $this->data['pdf']['font'] ?? null;

        if (! $family) {
            return $this->brand->pdfFont();
        }

        $fontsExtractor = new FontsExtractor;
        $fonts = $fontsExtractor->getFontsFromConfig();

        $font = array_merge($fonts->where('font-family', $family)->first(), [
            'name' => trim(explode(',', $family)[0]),
        ]);

        return $font;
    }

    /**
     * Get the document PDF size.
     */
    public function pdfSize()
    {
        return $this->data['pdf']['size'] ?? $this->brand->config['pdf']['size'] ?? 'a4';
    }

    /**
     * Get the document PDF orientation.
     */
    public function pdfOrientation()
    {
        return $this->data['pdf']['orientation'] ?? $this->brand->config['pdf']['orientation'] ?? 'landscape';
    }

    /**
     * Check whether all signers has signed the document
     */
    public function everyoneSigned(): bool
    {
        return $this->signers->filter->missingSignature()->isEmpty();
    }

    /**
     * Check whether at least one signer signed the document
     */
    public function atLeastOneSigned(): bool
    {
        return $this->signers->filter->hasSignature()->count() > 0;
    }

    /**
     * Get the model display name
     */
    public function displayName(): Attribute
    {
        return Attribute::get(fn () => $this->title);
    }

    /**
     * Get the document public URL
     */
    public function publicUrl(): Attribute
    {
        return Attribute::get(
            fn () => route('document.public', $this->uuid)
        );
    }

    /**
     * Get the URL path
     */
    public function path(): Attribute
    {
        return Attribute::get(
            fn () => "/documents/{$this->id}"
        );
    }

    /**
     * Get the document content
     */
    public function content(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => new DocumentContent($value, $this),
        );
    }

    /**
     * Provide the total column to be updated whenever the billable is updated
     */
    public function totalColumn(): string
    {
        return 'amount';
    }

    /**
     * Get the attributes that may contain pending media
     */
    public function attributesWithPendingMedia(): string
    {
        return 'content';
    }

    /**
     * Get the document available placeholders
     */
    public function placeholders(): BasePlaceholders
    {
        $default = new BasePlaceholders([
            GenericPlaceholder::make('company_name', fn () => config('app.name')),
            GenericPlaceholder::make('brand.display_name', fn () => $this->brand->display_name),
            GenericPlaceholder::make('brand.internal_name', fn () => $this->brand->name),
            GenericPlaceholder::make('document.title', fn () => $this->title)
                ->description(__('documents::document.document').' - '.__('documents::document.title')),
            GenericPlaceholder::make('document.type', fn () => $this->type->name)
                ->description(__('documents::document.document').' - '.__('documents::document.type.type')),
            GenericPlaceholder::make('document.owner', fn () => $this->user->name)
                ->description(__('documents::document.document').' - '.__('documents::fields.documents.user.name')),
            GenericPlaceholder::make('document.owner_email_address', fn () => $this->user->email)
                ->description(__('documents::document.document').' - '.__('core::app.owner_email_address')),
            DatePlaceholder::make('document.created_at', fn () => $this->created_at)
                ->description(__('documents::document.document').' - '.__('core::app.created_at')),
        ]);

        return $this->addPlaceholdersFromResources($default);
    }

    /**
     * Add placeholders from the resources
     */
    protected function addPlaceholdersFromResources(BasePlaceholders $placeholders): BasePlaceholders
    {
        foreach (static::resource()->availableAssociations() as $resource) {
            // Placeholders for first associated (primary record)
            $model = $this->exists ? $this->{$resource->associateableName()}()
                ->select($resource->newModel()->getKeyName())->first() : null;

            // When the placeholders are intended to be displayed, we will check if there is associated
            // model to use the merge fields to, if no model, no need to to add the fields
            if ($this->exists && ! $model) {
                continue;
            } elseif ($model) {
                // Re-query for display to eager load all the data
                $model = $resource->displayQuery()->find($model->getKey());
            }

            $placeholders->push(...collect((new ResourcePlaceholders($resource, $model))->all())
                ->each(function ($placeholder) use ($resource) {
                    $placeholder->description($resource->singularLabel().' - '.$placeholder->description);
                })->all());
        }

        return $placeholders;
    }

    /**
     * Get the document PDF filename.
     */
    public function pdfFilename(): string
    {
        return (Str::slug($this->type->name) ?: $this->type->name).'.pdf';
    }

    /**
     * Get the document PDF instance
     *
     * @return \Barryvdh\DomPDF\PDF
     */
    public function pdf()
    {
        return Pdf::loadView('documents::pdf', ['document' => $this])
            ->setPaper($this->pdfSize(), $this->pdfOrientation())
            ->setWarnings(false);
    }

    /**
     * Scope a query to include only documents due for sending.
     */
    public function scopeDueForSending(Builder $query): void
    {
        $query->whereNotNull('send_at')->where('send_at', '<=', Carbon::asAppTimezone());
    }

    /**
     * Get the timeline component for front-end
     */
    public function getTimelineComponent(): string
    {
        return 'record-tab-timeline-document';
    }

    /**
     * Mark the given document as lost
     */
    public function markAsLost(User $user): self|bool
    {
        if ($this->status === DocumentStatus::LOST || $this->status === DocumentStatus::ACCEPTED) {
            return false;
        }

        $this->status = DocumentStatus::LOST;
        $this->save();

        $this->addActivity([
            'lang' => [
                'key' => 'documents::document.activity.marked_as_lost',
                'attrs' => [
                    'user' => $user->name,
                ],
            ],
        ]);

        return $this;
    }

    /**
     * Mark the given document as accepted
     */
    public function markAsAccepted(User $user): self|bool
    {
        if ($this->status === DocumentStatus::ACCEPTED) {
            return false;
        }

        $this->forceFill([
            'status' => DocumentStatus::ACCEPTED,
            'accepted_at' => now(),
            'marked_accepted_by' => $user->getKey(),
        ])->save();

        $this->addActivity([
            'type' => 'success',
            'lang' => [
                'key' => 'documents::document.activity.marked_as_accepted',
                'attrs' => [
                    'user' => $user->name,
                ],
            ],
        ]);

        return $this;
    }

    /**
     * Mark the given document as draft
     */
    public function markAsDraft(User $user): self|bool
    {
        if ($this->status !== DocumentStatus::LOST &&
            $this->status === DocumentStatus::ACCEPTED && ! $this->marked_accepted_by) {
            return false;
        }

        $this->forceFill([
            'status' => DocumentStatus::DRAFT,
            'marked_accepted_by' => null,
        ])->save();

        $this->addActivity([
            'lang' => [
                'key' => 'documents::document.activity.marked_as_draft',
                'attrs' => [
                    'user' => $user->name,
                ],
            ],
        ]);

        return $this;
    }

    /**
     * Get localized config from the document brand.
     */
    public function localizedBrandConfig(string $key)
    {
        return $this->brand->getLocalizedConfig($key, $this->locale);
    }

    /**
     * Purge the document data.
     */
    public function purge(): void
    {
        if ($this->billable) {
            $this->billable->delete();
        }

        $this->signers()->delete();
        $this->deals()->withTrashed()->detach();
        $this->contacts()->withTrashed()->detach();
        $this->companies()->withTrashed()->detach();
    }

    /**
     * Eager load the relations that are required for the front end response.
     */
    public function scopeWithCommon(Builder $query): void
    {
        $query->with([
            'brand',
            'type',
            'signers',
            'user',
            'billable',
            'billable.products',
            'billable.products.originalProduct',
            'billable.products.billable',
            'contacts',
            'companies',
            'deals',
            'changelog',
        ]);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): DocumentFactory
    {
        return DocumentFactory::new();
    }
}
