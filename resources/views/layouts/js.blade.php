 <!--   Core JS Files   -->
 <script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
 <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
 <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>

 <!-- jQuery Scrollbar -->
 <script src="{{ asset('assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>

 <!-- Chart JS -->
 <script src="{{ asset('assets/js/plugin/chart.js/chart.min.js') }}"></script>

 <!-- jQuery Sparkline -->
 <script src="{{ asset('assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js') }}"></script>

 <!-- Chart Circle -->
 <script src="{{ asset('assets/js/plugin/chart-circle/circles.min.js') }}"></script>

 <!-- Datatables -->
 <script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>

 <!-- Bootstrap Notify -->
 <script src="{{ asset('assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

 <!-- bsSelectDrop Plugin -->
 <script src="{{ asset('assets/dist/jquery.bsSelectDrop.js') }}"></script>

 <!-- jQuery Vector Maps -->
 <script src="{{ asset('assets/js/plugin/jsvectormap/jsvectormap.min.js') }}"></script>
 <script src="{{ asset('assets/js/plugin/jsvectormap/world.js') }}"></script>

 <!-- Google Maps Plugin -->
 <script src="{{ asset('assets/js/plugin/gmaps/gmaps.js') }}"></script>

 <!-- Sweet Alert -->
 <script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>

 <!-- Kaiadmin JS -->
 <script src="{{ asset('assets/js/kaiadmin.min.js') }}"></script>
 <script>
     @if (Session::has('success'))
         $.notify({
             icon: 'fa fa-check',
             title: 'Success',
             message: "{{ Session::get('success') }}",
         }, {
             type: 'success',
             placement: {
                 from: "top",
                 align: "right"
             },
             time: 1000,
         });
     @endif
     @if (Session::has('error'))
         $.notify({
             icon: 'fa fa-times',
             title: 'Error',
             message: "{{ Session::get('error') }}",
         }, {
             type: 'danger',
             placement: {
                 from: "top",
                 align: "right"
             },
             time: 1000,
         });
     @endif
     @if (Session::has('info'))
         $.notify({
             icon: 'fa fa-info-circle',
             title: 'Info',
             message: "{{ Session::get('info') }}",
         }, {
             type: 'info',
             placement: {
                 from: "top",
                 align: "right"
             },
             time: 1000,
         });
     @endif
     @if (Session::has('warning'))
         $.notify({
             icon: 'fa fa-exclamation-triangle',
             title: 'Warning',
             message: "{{ Session::get('warning') }}",
         }, {
             type: 'warning',
             placement: {
                 from: "top",
                 align: "right"
             },
             time: 1000,
         });
     @endif

      $(document).ready(function() {
          if ($('[data-bs-toggle="select"]').length) {
              $('[data-bs-toggle="select"]').bsSelectDrop({
                  btnWidth: '100%',
                  search: true,
                  darkMenu: false
              });
          }

          $('#detailModal').on('show.bs.modal', function () {
              $('#btn-export-project-pdf').hide();
          });
      });
 </script>
 @stack('scripts')
