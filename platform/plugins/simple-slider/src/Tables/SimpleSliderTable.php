<?php

namespace Botble\SimpleSlider\Tables;

use Botble\Base\Facades\Html;
use Botble\SimpleSlider\Models\SimpleSlider;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\BulkChanges\CreatedAtBulkChange;
use Botble\Table\BulkChanges\NameBulkChange;
use Botble\Table\BulkChanges\StatusBulkChange;
use Botble\Table\BulkChanges\TextBulkChange;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;

class SimpleSliderTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(SimpleSlider::class)
            ->addHeaderAction(CreateHeaderAction::make()->route('simple-slider.create'))
            ->addColumns([
                IdColumn::make(),
                NameColumn::make()->route('simple-slider.edit'),
                FormattedColumn::make('key')
                    ->title(trans('plugins/simple-slider::simple-slider.shortcode'))
                    ->alignStart()
                    ->getValueUsing(function (FormattedColumn $column) {
                        $value = $column->getItem()->key;

                        if (! function_exists('shortcode')) {
                            return $value;
                        }

                        return shortcode()->generateShortcode('simple-slider', ['alias' => $value]);
                    })
                    ->renderUsing(fn (FormattedColumn $column) => Html::tag('code', $column->getValue()))
                    ->copyable(),
                FormattedColumn::make('country_name')
                    ->title(trans('plugins/simple-slider::simple-slider.country'))
                    ->alignStart(),

                CreatedAtColumn::make(),
                StatusColumn::make(),
            ])
            ->addActions([
                EditAction::make()->route('simple-slider.edit'),
                DeleteAction::make()->route('simple-slider.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('simple-slider.destroy'),
            ])
            ->addBulkChanges([
                NameBulkChange::make(),
                TextBulkChange::make()
                    ->name('key')
                    ->title(trans('plugins/simple-slider::simple-slider.key')),
                StatusBulkChange::make(),
                \Botble\Table\BulkChanges\SelectBulkChange::make()
                    ->name('country_id')
                    ->title(trans('plugins/simple-slider::simple-slider.country'))
                    ->choices(\Botble\Location\Models\Country::pluck('name', 'id')->toArray())
                    ->validate(['required', 'integer']),
                CreatedAtBulkChange::make(),
            ])
            ->queryUsing(function (Builder $query) {
                return $query
                    ->leftJoin('countries', 'simple_sliders.country_id', '=', 'countries.id')
                    ->select([
                        'simple_sliders.id',
                        'simple_sliders.name',
                        'simple_sliders.key',
                        'simple_sliders.status',
                        'simple_sliders.created_at',
                        'countries.name as country_name',
                    ]);
            });
    }
}
