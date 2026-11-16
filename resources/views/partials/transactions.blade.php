<div class="transaction-list">
@forelse($transactions as $transaction)
<article class="transaction-row">
 <span class="transaction-icon {{ $transaction->type }}">{{ $transaction->type==='pemasukan'?'↙':'↗' }}</span>
 <div class="transaction-desc"><strong>{{ $transaction->description }}</strong><small>{{ $transaction->category }} <span>·</span> {{ $transaction->date->format('d M Y') }}</small></div>
 <div class="transaction-value"><strong class="{{ $transaction->type }}">{{ $transaction->type==='pemasukan'?'+':'−' }} Rp {{ number_format($transaction->amount,0,',','.') }}</strong><small>{{ ucfirst($transaction->type) }}</small>@if($transaction->discount > 0)<small>Diskon Rp {{ number_format($transaction->discount,0,',','.') }}</small>@endif</div>
 <div class="transaction-tools"><div class="receipt">@if($transaction->attachment)<a href="{{ route('attachment',$transaction) }}" data-preview-attachment class="attachment" aria-label="View lampiran">View</a>@else<span class="muted">Tanpa lampiran</span>@endif</div>
 @if(request()->routeIs('transactions') && auth()->user()->role==='pengelola')
 <div class="row-actions">
 <button type="button" class="action-edit" data-edit="{{ json_encode(['id'=>$transaction->id,'date'=>$transaction->date->format('Y-m-d'),'type'=>$transaction->type,'category'=>$transaction->category,'amount'=>$transaction->amount,'discount'=>$transaction->discount,'description'=>$transaction->description,'attachment'=>(bool)$transaction->attachment]) }}">Edit</button>
 <button type="button" class="action-delete" data-delete="{{ $transaction->id }}" data-description="{{ $transaction->description }}">Hapus</button>
 </div>
 @endif
 </div>
</article>
@empty
<div class="empty"><span>⇄</span><h3>Belum ada transaksi</h3><p>Pencatatan yang rapi dimulai dari transaksi pertama.</p>@if(auth()->user()->role==='pengelola')@if(request()->routeIs('transactions'))<button type="button" class="btn" data-create>+ Catat transaksi</button>@else<a class="btn" href="{{ route('transactions') }}#catat">+ Catat transaksi</a>@endif @endif</div>
@endforelse
</div>
