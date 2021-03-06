=======================
Using Centreon packages
=======================

************
Installation
************

SELinux must be disabled. To do this, edit the file
**/etc/selinux/config** and replace *enforcing* with *disabled*::

    SELINUX=disabled

.. note::
    After saving the file, reboot your operating system to apply the
    changes.

Perform a quick check of SELinux status::

    $ getenforce
    Disabled

Add firewall rules or disable the firewall by running the following commands: ::

    # systemctl stop firewalld
    # systemctl disable firewalld
    # systemctl status firewalld

To install Centreon software from the repository, you should first install the
centreon-release package, which will provide the repository file.

Centreon repository installation::

    # wget http://yum.centreon.com/standard/18.10/el7/stable/noarch/RPMS/centreon-release-18.10-2.el7.centos.noarch.rpm -O /tmp/centreon-release-18.10-2.el7.centos.noarch.rpm
    # yum install --nogpgcheck /tmp/centreon-release-18.10-2.el7.centos.noarch.rpm

The repository is now installed.

Run the following command::

    # yum install centreon-poller-centreon-engine

.. include:: ssh_key.rst

.. include:: wizard_add_poller.rst
