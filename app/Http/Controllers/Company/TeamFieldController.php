<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamField;
use App\Models\User;
use Illuminate\Http\Request;

class TeamFieldController extends Controller
{
    private function companyId(): int
    {
        return auth()->user()->company_id;
    }

    public function store(Request $request, string $slug, Team $team)
    {
        abort_if($team->company_id !== $this->companyId(), 403);

        $data = $request->validate([
            'name'      => 'required|string|max:60',
            'type'      => 'required|in:text,number,date,single_select,multi_select,people,reference',
            'options'   => 'nullable|array',
            'options.*' => 'nullable|string|max:60',
        ]);

        $options = null;
        if (in_array($data['type'], ['single_select', 'multi_select'])) {
            $options = collect($data['options'] ?? [])
                ->map(fn ($o) => trim((string) $o))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($options)) {
                return back()
                    ->withErrors(['options' => 'Add at least one option label for a select field.'])
                    ->withInput();
            }
        }

        TeamField::create([
            'company_id' => $this->companyId(),
            'team_id'    => $team->id,
            'name'       => $data['name'],
            'type'       => $data['type'],
            'options'    => $options,
            'position'   => (int) $team->fields()->max('position') + 1,
        ]);

        return redirect()->route('company.team.overview', ['slug' => $slug, 'team' => $team, 'tab' => 'members'])
            ->with('success', 'Field added.');
    }

    public function destroy(string $slug, Team $team, TeamField $field)
    {
        abort_if($team->company_id !== $this->companyId(), 403);
        abort_if($field->team_id !== $team->id, 403);

        $field->delete();

        return redirect()->route('company.team.overview', ['slug' => $slug, 'team' => $team, 'tab' => 'members'])
            ->with('success', 'Field removed.');
    }

    public function setValue(Request $request, string $slug, Team $team, User $user)
    {
        abort_if($team->company_id !== $this->companyId(), 403);

        $data = $request->validate([
            'field_id' => 'required|integer',
            'value'    => 'nullable',
        ]);

        // Field must belong to this team.
        $fieldExists = TeamField::where('id', $data['field_id'])
            ->where('team_id', $team->id)
            ->where('company_id', $this->companyId())
            ->exists();
        abort_if(! $fieldExists, 403);

        // User must be a member of this team.
        $member = $team->members()->where('users.id', $user->id)->first();
        abort_if(! $member, 403);

        $values = $member->pivot->field_values ? json_decode($member->pivot->field_values, true) : [];
        if (! is_array($values)) {
            $values = [];
        }

        $value = $data['value'];
        if ($value === null || $value === '' || (is_array($value) && count($value) === 0)) {
            unset($values[$data['field_id']]);
        } else {
            $values[$data['field_id']] = $value;
        }

        $team->members()->updateExistingPivot($user->id, [
            'field_values' => json_encode($values),
        ]);

        return response()->json(['ok' => true]);
    }
}
