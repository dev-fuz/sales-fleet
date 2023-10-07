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

namespace Modules\Activities\Fields;

use Illuminate\Database\Eloquent\Builder;
use Modules\Activities\Models\Activity;
use Modules\Core\Facades\Format;
use Modules\Core\Fields\Date;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;
use Modules\Core\Support\Date\Carbon;
use Modules\Core\Support\Placeholders\DatePlaceholder;
use Modules\Core\Support\Placeholders\DateTimePlaceholder;
use Modules\Core\Table\Column;

class ActivityDueDate extends Date
{
    /**
     * The model attribute that hold the time
     */
    protected string $timeField = 'due_time';

    /**
     * The model attribute that holds the date
     */
    protected string $dateField = 'due_date';

    /**
     * The inline edit popover width (medium|large).
     */
    public string $inlineEditPanelWidth = 'large';

    /**
     * Field component.
     */
    public static $component = 'activity-due-date-field';

    /**
     * Initialize new ActivityDueDate instance class
     */
    public function __construct($label)
    {
        parent::__construct($this->dateField, $label);

        $this->tapIndexColumn(function (Column $column) {
            return $column->queryAs(Activity::dateTimeExpression($this->dateField, $this->timeField, $this->dateField))
                ->orderByUsing(function (Builder $query, string $direction) {
                    return $query->orderBy(
                        Activity::dateTimeExpression($this->dateField, $this->timeField),
                        $direction
                    );
                });
        })
            ->hideLabel()
            ->displayUsing(fn ($model, $value) => Format::separateDateAndTime(
                $model->{$this->dateField},
                $model->{$this->timeField},
                $model->user
            ))
            ->provideSampleValueUsing(fn () => date('Y-m-d').' 08:00:00')
            ->fillUsing(function (Model $model, string $attribute, ResourceRequest $request, mixed $value, string $requestAttribute) {
                $model->{$this->dateField} = $request->input($this->dateField);
                $model->{$this->timeField} = $this->ensureTimeAttributeHasSeconds($request->input($this->timeField));
            })
            ->prepareForValidation(
                fn (mixed $value, ResourceRequest $request) => $this->parsePreValidationValue($value, $request)
            );
    }

    /**
     * Resolve the field value for JSON Resource
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array
     */
    public function resolveForJsonResource($model)
    {
        return [
            $this->attribute => [
                'date' => Carbon::parse($this->resolve($model))->format('Y-m-d'),
                'time' => $this->getTimeValue($model),
            ],
        ];
    }

    /**
     * Resolve the field value for export
     *
     * @param  \Modules\Core\Models\Model  $model
     * @return string|null
     */
    public function resolveForExport($model)
    {
        $time = $this->getTimeValue($model);

        $carbonInstance = $this->dateTimeToCarbon($model->{$this->dateField}, $time);

        return $carbonInstance->format('Y-m-d'.($time ? ' H:i:s' : ''));
    }

    /**
     * Get the mailable template placeholder
     *
     * @param  \Modules\Core\Models\Model|null  $model
     * @return \Modules\Core\Support\Placeholders\DatePlaceholder|\Modules\Core\Support\Placeholders\DateTimePlaceholder
     */
    public function mailableTemplatePlaceholder($model)
    {
        $placeholderClass = $model?->{$this->timeField} ?
            DateTimePlaceholder::class :
            DatePlaceholder::class;

        return $placeholderClass::make($this->attribute)
            ->formatUsing(fn () => $this->resolveForDisplay($model))
            ->description($this->label);
    }

    /**
     * Create separate and and time attributes from the given value
     *
     * @param  string|null  $value
     * @param  \Modules\Core\Http\Requests\ResourceRequest  $request
     * @param  string|null  $dateAttribute
     * @param  string|null  $timeAttribute
     * @return array
     */
    protected function createSeparateDateAndTimeAttributes($value, $request, $dateAttribute = null, $timeAttribute = null)
    {
        $dateAttribute = ($dateAttribute ?: $this->dateField);
        $timeAttribute = ($timeAttribute ?: $this->timeField);

        [$date, $time] = [$value, null];

        if (Carbon::isISO8601($value)) {
            $value = Carbon::parse($value)->inAppTimezone();
        }

        if (! is_null($value) && str_contains($value, ' ')) {
            [$date, $time] = explode(' ', $value);
        }

        // Overrides if the date is provided in full e.q. 2021-12-14 12:00:00
        // and the user provide time field e.q. 14:00:00 the 14:00:00 will be used
        if (! $time && $request->has($timeAttribute)) {
            $time = $request->{$timeAttribute};
        }

        return [
            $dateAttribute => $date,
            $timeAttribute => $time,
        ];
    }

    /**
     * Parse the value before validation.
     *
     * @param  mixed  $value
     * @return string|null
     */
    protected function parsePreValidationValue($value, ResourceRequest $request)
    {
        // If both fields are present, we will just return the actual provided field date value
        if ($request->has([$this->dateField, $this->timeField])) {
            return $request->input($this->dateField);
        }

        $attributes = $this->createSeparateDateAndTimeAttributes($value, $request);

        // When provided the field as full date and time e.q. 2021-12-14 12:00:00 the time field
        // will be missing in the request, we need to merge it with the other fields
        if ($request->missing($this->timeField)) {
            $request->merge([$this->timeField => $attributes[$this->timeField]]);
        }

        // Return the actul date value from the parsed attributes
        return $attributes[$this->dateField];
    }

    /**
     * Get the time value from the model
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string|null
     */
    protected function getTimeValue($model)
    {
        if (! $model->{$this->timeField}) {
            return null;
        }

        return $this->dateTimeToCarbon(
            $this->resolve($model),
            $model->{$this->timeField}
        )->format('H:i');
    }

    /**
     * Create Carbon UTC instance from the given date and time
     *
     * @param  string  $date
     * @param  string|null  $time
     * @return \Carbon\Carbon
     */
    protected function dateTimeToCarbon($date, $time)
    {
        return Carbon::parse(
            Carbon::parse($date)->format('Y-m-d').($time ? ' '.$time : '')
        );
    }

    /**
     * Ensure the given time attribute value has seconds.
     */
    protected function ensureTimeAttributeHasSeconds($value): mixed
    {
        if (! $value) {
            return $value;
        }

        if ($this->missingSeconds($value)) {
            return $value.':00';
        }

        return $value;
    }

    /**
     * Check if the given time string has seconds.
     */
    protected function missingSeconds(string $timeString): bool
    {
        return preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $timeString) === 1;
    }
}
