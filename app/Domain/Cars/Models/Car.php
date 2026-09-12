<?php

declare(strict_types=1);

namespace App\Domain\Cars\Models;

use App\Domain\Voting\Models\Vote;
use Database\Factories\CarFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $source_auction_item_id
 * @property string|null $auction_id
 * @property string $make
 * @property string $model
 * @property int $year
 * @property int|null $odometer
 * @property string|null $units
 * @property string|null $engine
 * @property string|null $transmission
 * @property string|null $color
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int $votes_count
 * @property-read Collection<int, CarPhoto> $photos
 * @property-read Collection<int, Vote> $wonVotes
 */
#[UseFactory(CarFactory::class)]
final class Car extends Model
{
    /** @use HasFactory<CarFactory> */
    use HasFactory;

    public const string FIELD_ID = 'id';

    public const string FIELD_SOURCE_AUCTION_ITEM_ID = 'source_auction_item_id';

    public const string FIELD_AUCTION_ID = 'auction_id';

    public const string FIELD_MAKE = 'make';

    public const string FIELD_MODEL = 'model';

    public const string FIELD_YEAR = 'year';

    public const string FIELD_ODOMETER = 'odometer';

    public const string FIELD_UNITS = 'units';

    public const string FIELD_ENGINE = 'engine';

    public const string FIELD_TRANSMISSION = 'transmission';

    public const string FIELD_COLOR = 'color';

    public const string FIELD_CREATED_AT = 'created_at';

    public const string FIELD_UPDATED_AT = 'updated_at';

    /** @var list<string> */
    protected $fillable = [
        self::FIELD_SOURCE_AUCTION_ITEM_ID,
        self::FIELD_AUCTION_ID,
        self::FIELD_MAKE,
        self::FIELD_MODEL,
        self::FIELD_YEAR,
        self::FIELD_ODOMETER,
        self::FIELD_UNITS,
        self::FIELD_ENGINE,
        self::FIELD_TRANSMISSION,
        self::FIELD_COLOR,
    ];

    public function photos(): HasMany
    {
        return $this->hasMany(CarPhoto::class);
    }

    public function wonVotes(): HasMany
    {
        return $this->hasMany(Vote::class, Vote::FIELD_WINNER_CAR_ID);
    }

    public function scopeSelectModelKey(Builder $query): void
    {
        $query->selectRaw($this->modelKeyExpression() . ' AS model_key');
    }

    public function scopeWhereModelKey(Builder $query, string $modelKey): void
    {
        $query->whereRaw($this->modelKeyExpression() . ' = ?', [$modelKey]);
    }

    protected function casts(): array
    {
        return [
            self::FIELD_YEAR     => 'integer',
            self::FIELD_ODOMETER => 'integer',
        ];
    }

    private function modelKeyExpression(): string
    {
        $grammar = $this->getConnection()->getQueryGrammar();
        $make = $this->wrapColumn($grammar, $this->qualifyColumn(self::FIELD_MAKE));
        $model = $this->wrapColumn($grammar, $this->qualifyColumn(self::FIELD_MODEL));

        return "CONCAT({$make}, ' ', {$model})";
    }

    private function wrapColumn(Grammar $grammar, string $column): string
    {
        return $grammar->wrap($column);
    }
}
