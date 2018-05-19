@if((sizeof($files) > 0) || (sizeof($directories) > 0))
<table class="table table-responsive table-condensed table-striped hidden-xs">
  <thead>
    <th style='width:50%;'>{{ Lang::get('laravel-filemanager::lfm.title-item') }}</th>
    <th>URL</th>
    <th>Anonymouse</th>
    <th>Authenticated User</th>
    <th>Premium</th>
    <th>VIP</th>
    <th>Administrator</th>
    <th>{{ Lang::get('laravel-filemanager::lfm.title-size') }}</th>
    <th>{{ Lang::get('laravel-filemanager::lfm.title-type') }}</th>
    <th>{{ Lang::get('laravel-filemanager::lfm.title-modified') }}</th>
    <th>{{ Lang::get('laravel-filemanager::lfm.title-action') }}</th>
  </thead>
  <tbody>
    @foreach($items as $item)
    <tr>
      <td>
        <i class="fa {{ $item->icon }}"></i>
        <a class="{{ $item->is_file ? 'file' : 'folder'}}-item clickable" data-id="{{ $item->is_file ? $item->url : $item->path }}">
          {{ str_limit($item->name, $limit = 20, $end = '...') }}
        </a>
      </td>
      <td>@if($item->is_file && !in_array(pathinfo($item->name, PATHINFO_EXTENSION), [])) {{ $item->download }} @endif</td>
      <td>@if($item->is_file && !in_array(pathinfo($item->name, PATHINFO_EXTENSION), []))<a href="javascript:changePerm('anonymouse', '{{ ($item->permission['anonymouse']) ? 'disallow' : 'allow' }}', '{{ ($item->permission['id']) }}')"><i class="fa fa-circle" style="color: {{ $item->permission['anonymouse'] ? 'green' : 'red' }}"></i></a> @endif</td>
      <td>@if($item->is_file && !in_array(pathinfo($item->name, PATHINFO_EXTENSION), []))<a href="javascript:changePerm('authenticated_user', '{{ ($item->permission['authenticated_user']) ? 'disallow' : 'allow' }}', '{{ ($item->permission['id']) }}')"><i class="fa fa-circle" style="color: {{ $item->permission['authenticated_user'] ? 'green' : 'red' }}"></i></a> @endif</td>
      <td>@if($item->is_file && !in_array(pathinfo($item->name, PATHINFO_EXTENSION), []))<a href="javascript:changePerm('premium', '{{ ($item->permission['premium']) ? 'disallow' : 'allow' }}', '{{ ($item->permission['id']) }}')"><i class="fa fa-circle" style="color: {{ $item->permission['premium'] ? 'green' : 'red' }}"></i></a> @endif</td>
      <td>@if($item->is_file && !in_array(pathinfo($item->name, PATHINFO_EXTENSION), []))<a href="javascript:changePerm('vip', '{{ ($item->permission['vip']) ? 'disallow' : 'allow' }}', '{{ ($item->permission['id']) }}')"><i class="fa fa-circle" style="color: {{ $item->permission['vip'] ? 'green' : 'red' }}"></i></a> @endif</td>
      <td>@if($item->is_file && !in_array(pathinfo($item->name, PATHINFO_EXTENSION), []))<a href="javascript:changePerm('administrator', '{{ ($item->permission['administrator']) ? 'disallow' : 'allow' }}', '{{ ($item->permission['id']) }}')"><i class="fa fa-circle" style="color: {{ $item->permission['administrator'] ? 'green' : 'red' }}"></i></a> @endif</td>
      <td>{{ $item->size }}</td>
      <td>{{ $item->type }}</td>
      <td>{{ $item->time }}</td>
      <td>
        @if($item->is_file)
        <a href="javascript:trash('{{ $item->name }}')">
          <i class="fa fa-trash fa-fw"></i>
        </a>
        @if(in_array(pathinfo($item->name, PATHINFO_EXTENSION), ['mp4', 'mkv']))
        <a href="javascript:encode('{{ $item->pathForEncode }}')">
          <i class="fa fa-video-camera fa-fw"></i>
        </a>
        @endif
        @if($item->thumb)
        <a href="javascript:cropImage('{{ $item->name }}')">
          <i class="fa fa-crop fa-fw"></i>
        </a>
        <a href="javascript:resizeImage('{{ $item->name }}')">
          <i class="fa fa-arrows fa-fw"></i>
        </a>
        @endif
        @endif
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

<table class="table visible-xs">
  <tbody>
    @foreach($items as $item)
    <tr>
      <td>
        <div class="media" style="height: 70px;">
          <div class="media-left">
            <div class="square {{ $item->is_file ? 'file' : 'folder'}}-item clickable"  data-id="{{ $item->is_file ? $item->url : $item->path }}">
              @if($item->thumb)
              <img src="{{ $item->thumb }}">
              @else
              <i class="fa {{ $item->icon }} fa-5x"></i>
              @endif
            </div>
          </div>
          <div class="media-body" style="padding-top: 10px;">
            <div class="media-heading">
              <p>
                <a class="{{ $item->is_file ? 'file' : 'folder'}}-item clickable" data-id="{{ $item->is_file ? $item->url : $item->path }}">
                  {{ str_limit($item->name, $limit = 20, $end = '...') }}
                </a>
                &nbsp;&nbsp;
                {{-- <a href="javascript:rename('{{ $item->name }}')">
                  <i class="fa fa-edit"></i>
                </a> --}}
              </p>
            </div>
            <p style="color: #aaa;font-weight: 400">{{ $item->time }}</p>
          </div>
        </div>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

@else
<p>{{ trans('laravel-filemanager::lfm.message-empty') }}</p>
@endif
