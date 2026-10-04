<?php

$content = file_get_contents('resources/views/presentations/show.blade.php');

// Replace card Topic
$cardTopicOld = '@if($pres->thesis && $pres->thesis->title)
                                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-700 font-medium line-clamp-2" title="{{ $pres->thesis->title }}">
                                        <span class="font-bold text-slate-900">Topic:</span> {{ $pres->thesis->title }}
                                    </div>
                                @endif';

$cardTopicNew = '@if($pres->thesis)
                                    @if(Auth::user()->hasRole(\'Student\') && Auth::user()->studentProfile?->id === $pres->thesis->student_profile_id)
                                        <form action="{{ route(\'theses.update\', $pres->thesis) }}" method="POST" class="mt-2 flex items-center gap-2 w-full">
                                            @csrf
                                            @method(\'PATCH\')
                                            <input type="text" name="title" value="{{ $pres->thesis->title }}" class="flex-1 px-3 py-1.5 text-xs rounded-lg bg-slate-50 border border-slate-200 focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 transition-colors" required placeholder="Enter thesis title...">
                                            <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-[10px] font-black uppercase tracking-wider hover:bg-slate-800 transition-colors whitespace-nowrap">Save</button>
                                        </form>
                                    @elseif($pres->thesis->title)
                                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-700 font-medium line-clamp-2" title="{{ $pres->thesis->title }}">
                                            <span class="font-bold text-slate-900">Topic:</span> {{ $pres->thesis->title }}
                                        </div>
                                    @endif
                                @endif';

$content = str_replace($cardTopicOld, $cardTopicNew, $content);

// Replace Table Topic
$tableTopicOld = '<td class="px-4 py-3.5 max-w-xs">
                                        <p class="font-medium text-slate-800 line-clamp-2" title="{{ $topic }}">
                                            {{ $topic }}
                                        </p>
                                    </td>';

$tableTopicNew = '<td class="px-4 py-3.5 max-w-xs">
                                        @if(Auth::user()->hasRole(\'Student\') && Auth::user()->studentProfile?->id === $sm->thesis?->student_profile_id)
                                            <form action="{{ route(\'theses.update\', $sm->thesis) }}" method="POST" class="flex flex-col gap-1 w-full">
                                                @csrf
                                                @method(\'PATCH\')
                                                <input type="text" name="title" value="{{ $sm->thesis->title }}" class="w-full px-2 py-1 text-[11px] rounded bg-white border border-slate-200 focus:border-primary-500 focus:ring focus:ring-primary-200 transition-colors" required placeholder="Enter title...">
                                                <button type="submit" class="self-start px-2 py-1 bg-slate-100 border border-slate-200 text-slate-700 hover:bg-slate-200 rounded text-[9px] font-bold uppercase transition-colors">Update</button>
                                            </form>
                                        @else
                                            <p class="font-medium text-slate-800 line-clamp-2" title="{{ $topic }}">
                                                {{ $topic }}
                                            </p>
                                        @endif
                                    </td>';

$content = str_replace($tableTopicOld, $tableTopicNew, $content);

file_put_contents('resources/views/presentations/show.blade.php', $content);
