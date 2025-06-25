<textarea class="d-none" id="currencies" name="currencies">{!! json_encode($currencies) !!}</textarea>
<textarea class="d-none" id="deleted_currencies" name="deleted_currencies"></textarea>
@foreach ($currencies as $currency)
    <tr>
        <td>{{ $currency->title }}</td>
        <td>{{ $currency->symbol }}</td>
        <td>
            <select name="currencies2[][{{ $currency->id }}][country_id]" class="form-control">
                <option value="">-- Select Country --</option>
                @foreach (get_countries() as $countryId => $countryName)
                    <option value="{{ $countryId }}"
                        @if ($currency->country_id == $countryId) selected @endif>
                        {{ $countryName }}
                    </option>
                @endforeach
            </select>
        </td>
    </tr>
@endforeach