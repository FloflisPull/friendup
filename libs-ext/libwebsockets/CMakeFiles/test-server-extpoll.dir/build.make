# CMAKE generated file: DO NOT EDIT!
# Generated by "Unix Makefiles" Generator, CMake Version 3.5

# Delete rule output on recipe failure.
.DELETE_ON_ERROR:


#=============================================================================
# Special targets provided by cmake.

# Disable implicit rules so canonical targets will work.
.SUFFIXES:


# Remove some rules from gmake that .SUFFIXES does not remove.
SUFFIXES =

.SUFFIXES: .hpux_make_needs_suffix_list


# Suppress display of executed commands.
$(VERBOSE).SILENT:


# A target that is always out of date.
cmake_force:

.PHONY : cmake_force

#=============================================================================
# Set environment variables for the build.

# The shell in which to execute make rules.
SHELL = /bin/sh

# The CMake executable.
CMAKE_COMMAND = /usr/bin/cmake

# The command to remove a file.
RM = /usr/bin/cmake -E remove -f

# Escaping for special characters.
EQUALS = =

# The top-level source directory on which CMake was run.
CMAKE_SOURCE_DIR = /home/thomas/opensource/friendup/libs-ext/libwebsockets

# The top-level build directory on which CMake was run.
CMAKE_BINARY_DIR = /home/thomas/opensource/friendup/libs-ext/libwebsockets

# Include any dependencies generated for this target.
include CMakeFiles/test-server-extpoll.dir/depend.make

# Include the progress variables for this target.
include CMakeFiles/test-server-extpoll.dir/progress.make

# Include the compile flags for this target's objects.
include CMakeFiles/test-server-extpoll.dir/flags.make

CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.o: CMakeFiles/test-server-extpoll.dir/flags.make
CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.o: test-server/test-server.c
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green --progress-dir=/home/thomas/opensource/friendup/libs-ext/libwebsockets/CMakeFiles --progress-num=$(CMAKE_PROGRESS_1) "Building C object CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.o"
	/usr/bin/cc  $(C_DEFINES) $(C_INCLUDES) $(C_FLAGS) -o CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.o   -c /home/thomas/opensource/friendup/libs-ext/libwebsockets/test-server/test-server.c

CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.i: cmake_force
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green "Preprocessing C source to CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.i"
	/usr/bin/cc  $(C_DEFINES) $(C_INCLUDES) $(C_FLAGS) -E /home/thomas/opensource/friendup/libs-ext/libwebsockets/test-server/test-server.c > CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.i

CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.s: cmake_force
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green "Compiling C source to assembly CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.s"
	/usr/bin/cc  $(C_DEFINES) $(C_INCLUDES) $(C_FLAGS) -S /home/thomas/opensource/friendup/libs-ext/libwebsockets/test-server/test-server.c -o CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.s

CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.o.requires:

.PHONY : CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.o.requires

CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.o.provides: CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.o.requires
	$(MAKE) -f CMakeFiles/test-server-extpoll.dir/build.make CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.o.provides.build
.PHONY : CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.o.provides

CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.o.provides.build: CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.o


CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.o: CMakeFiles/test-server-extpoll.dir/flags.make
CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.o: test-server/test-server-http.c
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green --progress-dir=/home/thomas/opensource/friendup/libs-ext/libwebsockets/CMakeFiles --progress-num=$(CMAKE_PROGRESS_2) "Building C object CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.o"
	/usr/bin/cc  $(C_DEFINES) $(C_INCLUDES) $(C_FLAGS) -o CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.o   -c /home/thomas/opensource/friendup/libs-ext/libwebsockets/test-server/test-server-http.c

CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.i: cmake_force
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green "Preprocessing C source to CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.i"
	/usr/bin/cc  $(C_DEFINES) $(C_INCLUDES) $(C_FLAGS) -E /home/thomas/opensource/friendup/libs-ext/libwebsockets/test-server/test-server-http.c > CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.i

CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.s: cmake_force
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green "Compiling C source to assembly CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.s"
	/usr/bin/cc  $(C_DEFINES) $(C_INCLUDES) $(C_FLAGS) -S /home/thomas/opensource/friendup/libs-ext/libwebsockets/test-server/test-server-http.c -o CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.s

CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.o.requires:

.PHONY : CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.o.requires

CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.o.provides: CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.o.requires
	$(MAKE) -f CMakeFiles/test-server-extpoll.dir/build.make CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.o.provides.build
.PHONY : CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.o.provides

CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.o.provides.build: CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.o


CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.o: CMakeFiles/test-server-extpoll.dir/flags.make
CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.o: test-server/test-server-dumb-increment.c
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green --progress-dir=/home/thomas/opensource/friendup/libs-ext/libwebsockets/CMakeFiles --progress-num=$(CMAKE_PROGRESS_3) "Building C object CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.o"
	/usr/bin/cc  $(C_DEFINES) $(C_INCLUDES) $(C_FLAGS) -o CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.o   -c /home/thomas/opensource/friendup/libs-ext/libwebsockets/test-server/test-server-dumb-increment.c

CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.i: cmake_force
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green "Preprocessing C source to CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.i"
	/usr/bin/cc  $(C_DEFINES) $(C_INCLUDES) $(C_FLAGS) -E /home/thomas/opensource/friendup/libs-ext/libwebsockets/test-server/test-server-dumb-increment.c > CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.i

CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.s: cmake_force
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green "Compiling C source to assembly CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.s"
	/usr/bin/cc  $(C_DEFINES) $(C_INCLUDES) $(C_FLAGS) -S /home/thomas/opensource/friendup/libs-ext/libwebsockets/test-server/test-server-dumb-increment.c -o CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.s

CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.o.requires:

.PHONY : CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.o.requires

CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.o.provides: CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.o.requires
	$(MAKE) -f CMakeFiles/test-server-extpoll.dir/build.make CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.o.provides.build
.PHONY : CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.o.provides

CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.o.provides.build: CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.o


CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.o: CMakeFiles/test-server-extpoll.dir/flags.make
CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.o: test-server/test-server-mirror.c
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green --progress-dir=/home/thomas/opensource/friendup/libs-ext/libwebsockets/CMakeFiles --progress-num=$(CMAKE_PROGRESS_4) "Building C object CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.o"
	/usr/bin/cc  $(C_DEFINES) $(C_INCLUDES) $(C_FLAGS) -o CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.o   -c /home/thomas/opensource/friendup/libs-ext/libwebsockets/test-server/test-server-mirror.c

CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.i: cmake_force
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green "Preprocessing C source to CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.i"
	/usr/bin/cc  $(C_DEFINES) $(C_INCLUDES) $(C_FLAGS) -E /home/thomas/opensource/friendup/libs-ext/libwebsockets/test-server/test-server-mirror.c > CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.i

CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.s: cmake_force
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green "Compiling C source to assembly CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.s"
	/usr/bin/cc  $(C_DEFINES) $(C_INCLUDES) $(C_FLAGS) -S /home/thomas/opensource/friendup/libs-ext/libwebsockets/test-server/test-server-mirror.c -o CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.s

CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.o.requires:

.PHONY : CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.o.requires

CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.o.provides: CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.o.requires
	$(MAKE) -f CMakeFiles/test-server-extpoll.dir/build.make CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.o.provides.build
.PHONY : CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.o.provides

CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.o.provides.build: CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.o


CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.o: CMakeFiles/test-server-extpoll.dir/flags.make
CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.o: test-server/test-server-status.c
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green --progress-dir=/home/thomas/opensource/friendup/libs-ext/libwebsockets/CMakeFiles --progress-num=$(CMAKE_PROGRESS_5) "Building C object CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.o"
	/usr/bin/cc  $(C_DEFINES) $(C_INCLUDES) $(C_FLAGS) -o CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.o   -c /home/thomas/opensource/friendup/libs-ext/libwebsockets/test-server/test-server-status.c

CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.i: cmake_force
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green "Preprocessing C source to CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.i"
	/usr/bin/cc  $(C_DEFINES) $(C_INCLUDES) $(C_FLAGS) -E /home/thomas/opensource/friendup/libs-ext/libwebsockets/test-server/test-server-status.c > CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.i

CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.s: cmake_force
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green "Compiling C source to assembly CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.s"
	/usr/bin/cc  $(C_DEFINES) $(C_INCLUDES) $(C_FLAGS) -S /home/thomas/opensource/friendup/libs-ext/libwebsockets/test-server/test-server-status.c -o CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.s

CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.o.requires:

.PHONY : CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.o.requires

CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.o.provides: CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.o.requires
	$(MAKE) -f CMakeFiles/test-server-extpoll.dir/build.make CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.o.provides.build
.PHONY : CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.o.provides

CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.o.provides.build: CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.o


CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.o: CMakeFiles/test-server-extpoll.dir/flags.make
CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.o: test-server/test-server-echogen.c
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green --progress-dir=/home/thomas/opensource/friendup/libs-ext/libwebsockets/CMakeFiles --progress-num=$(CMAKE_PROGRESS_6) "Building C object CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.o"
	/usr/bin/cc  $(C_DEFINES) $(C_INCLUDES) $(C_FLAGS) -o CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.o   -c /home/thomas/opensource/friendup/libs-ext/libwebsockets/test-server/test-server-echogen.c

CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.i: cmake_force
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green "Preprocessing C source to CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.i"
	/usr/bin/cc  $(C_DEFINES) $(C_INCLUDES) $(C_FLAGS) -E /home/thomas/opensource/friendup/libs-ext/libwebsockets/test-server/test-server-echogen.c > CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.i

CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.s: cmake_force
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green "Compiling C source to assembly CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.s"
	/usr/bin/cc  $(C_DEFINES) $(C_INCLUDES) $(C_FLAGS) -S /home/thomas/opensource/friendup/libs-ext/libwebsockets/test-server/test-server-echogen.c -o CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.s

CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.o.requires:

.PHONY : CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.o.requires

CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.o.provides: CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.o.requires
	$(MAKE) -f CMakeFiles/test-server-extpoll.dir/build.make CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.o.provides.build
.PHONY : CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.o.provides

CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.o.provides.build: CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.o


# Object files for target test-server-extpoll
test__server__extpoll_OBJECTS = \
"CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.o" \
"CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.o" \
"CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.o" \
"CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.o" \
"CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.o" \
"CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.o"

# External object files for target test-server-extpoll
test__server__extpoll_EXTERNAL_OBJECTS =

bin/libwebsockets-test-server-extpoll: CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.o
bin/libwebsockets-test-server-extpoll: CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.o
bin/libwebsockets-test-server-extpoll: CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.o
bin/libwebsockets-test-server-extpoll: CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.o
bin/libwebsockets-test-server-extpoll: CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.o
bin/libwebsockets-test-server-extpoll: CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.o
bin/libwebsockets-test-server-extpoll: CMakeFiles/test-server-extpoll.dir/build.make
bin/libwebsockets-test-server-extpoll: lib/libwebsockets.a
bin/libwebsockets-test-server-extpoll: /usr/lib/x86_64-linux-gnu/libz.so
bin/libwebsockets-test-server-extpoll: /usr/lib/x86_64-linux-gnu/libssl.so
bin/libwebsockets-test-server-extpoll: /usr/lib/x86_64-linux-gnu/libcrypto.so
bin/libwebsockets-test-server-extpoll: CMakeFiles/test-server-extpoll.dir/link.txt
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green --bold --progress-dir=/home/thomas/opensource/friendup/libs-ext/libwebsockets/CMakeFiles --progress-num=$(CMAKE_PROGRESS_7) "Linking C executable bin/libwebsockets-test-server-extpoll"
	$(CMAKE_COMMAND) -E cmake_link_script CMakeFiles/test-server-extpoll.dir/link.txt --verbose=$(VERBOSE)

# Rule to build all files generated by this target.
CMakeFiles/test-server-extpoll.dir/build: bin/libwebsockets-test-server-extpoll

.PHONY : CMakeFiles/test-server-extpoll.dir/build

CMakeFiles/test-server-extpoll.dir/requires: CMakeFiles/test-server-extpoll.dir/test-server/test-server.c.o.requires
CMakeFiles/test-server-extpoll.dir/requires: CMakeFiles/test-server-extpoll.dir/test-server/test-server-http.c.o.requires
CMakeFiles/test-server-extpoll.dir/requires: CMakeFiles/test-server-extpoll.dir/test-server/test-server-dumb-increment.c.o.requires
CMakeFiles/test-server-extpoll.dir/requires: CMakeFiles/test-server-extpoll.dir/test-server/test-server-mirror.c.o.requires
CMakeFiles/test-server-extpoll.dir/requires: CMakeFiles/test-server-extpoll.dir/test-server/test-server-status.c.o.requires
CMakeFiles/test-server-extpoll.dir/requires: CMakeFiles/test-server-extpoll.dir/test-server/test-server-echogen.c.o.requires

.PHONY : CMakeFiles/test-server-extpoll.dir/requires

CMakeFiles/test-server-extpoll.dir/clean:
	$(CMAKE_COMMAND) -P CMakeFiles/test-server-extpoll.dir/cmake_clean.cmake
.PHONY : CMakeFiles/test-server-extpoll.dir/clean

CMakeFiles/test-server-extpoll.dir/depend:
	cd /home/thomas/opensource/friendup/libs-ext/libwebsockets && $(CMAKE_COMMAND) -E cmake_depends "Unix Makefiles" /home/thomas/opensource/friendup/libs-ext/libwebsockets /home/thomas/opensource/friendup/libs-ext/libwebsockets /home/thomas/opensource/friendup/libs-ext/libwebsockets /home/thomas/opensource/friendup/libs-ext/libwebsockets /home/thomas/opensource/friendup/libs-ext/libwebsockets/CMakeFiles/test-server-extpoll.dir/DependInfo.cmake --color=$(COLOR)
.PHONY : CMakeFiles/test-server-extpoll.dir/depend

